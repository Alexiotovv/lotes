<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cronograma;
use App\Models\Pago;
use App\Models\Caja;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagosAgrupadosController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::with(['ventas' => function($q) {
            $q->with(['lote', 'cronogramas' => function($q2) {
                $q2->where('estado', 'pendiente')->orWhere('estado', 'vencido');
            }]);
        }]);
        // Búsqueda por cliente
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_cliente', 'LIKE', "%$search%")
                  ->orWhere('dni_ruc', 'LIKE', "%$search%");
            });
        }

        $clientes = $query->whereHas('ventas.cronogramas', function($q) {
            $q->where('estado', 'pendiente')->orWhere('estado', 'vencido');
        })->latest()->paginate(15)->appends($request->query());

        $cajas = Caja::where('activo', true)->get();
        return view('pagos-agrupados.index', compact('clientes', 'cajas'));
    }

    public function getVentasCliente(Cliente $cliente)
    {
        try {
            $ventas = $cliente->ventas()->with(['lote', 'cronogramas' => function($q) {
                $q->whereIn('estado', ['pendiente', 'vencido'])
                ->orderBy('fecha_pago') // Ordenar por fecha para obtener la primera
                ->orderBy('nro_cuota'); // Y por número de cuota como segundo criterio
            }])->get();

            $ventasData = $ventas->map(function($venta) {
                // Obtener solo la primera cuota pendiente
                $primeraCuota = $venta->cronogramas->first();
                
                if (!$primeraCuota) {
                    return null; // No hay cuotas pendientes
                }
                
                $pagado = $primeraCuota->pagos->sum('monto_pagado');
                $pendiente = max(0, $primeraCuota->cuota - $pagado);
                
                // Si la cuota ya está pagada completamente, saltar
                if ($pendiente <= 0) {
                    return null;
                }
                
                return [
                    'id' => $venta->id,
                    'lote_codigo' => $venta->lote->codigo ?? 'N/A',
                    'lote_nombre' => $venta->lote->nombre ?? 'N/A',
                    'total_venta' => ($venta->lote->area_m2 ?? 0) * ($venta->lote->precio_m2 ?? 0),
                    'cuota_pendiente' => [
                        'id' => $primeraCuota->id,
                        'nro_cuota' => $primeraCuota->nro_cuota,
                        'fecha_pago' => $primeraCuota->fecha_pago,
                        'cuota_total' => (float) $primeraCuota->cuota,
                        'pagado' => (float) $pagado,
                        'pendiente' => (float) $pendiente,
                        'estado' => $primeraCuota->estado,
                        'venta_id' => $primeraCuota->venta_id,
                        'total_cuotas' => $venta->cronogramas->count(), // Total de cuotas para información
                        'cuotas_pendientes_restantes' => $venta->cronogramas->where('estado', 'pendiente')->count() - 1 // Resto de cuotas pendientes
                    ],
                    'total_pendiente' => (float) $venta->cronogramas->sum(function($c) {
                        $pagado = $c->pagos->sum('monto_pagado');
                        return max(0, $c->cuota - $pagado);
                    })
                ];
            })->filter(function($venta) {
                return $venta !== null; // Filtrar ventas sin cuotas pendientes
            })->values();

            return response()->json($ventasData);

        } catch (\Exception $e) {
            \Log::error('Error en getVentasCliente: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cargar las ventas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        \Log::info('Datos recibidos en store:', $request->all());
        
        // Si pagos viene como string JSON, decodificarlo
        $pagosInput = $request->input('pagos');
        
        if (is_string($pagosInput)) {
            try {
                $pagosDecoded = json_decode($pagosInput, true, 512, JSON_THROW_ON_ERROR);
                $request->merge(['pagos' => $pagosDecoded]);
                \Log::info('Pagos decodificados:', $pagosDecoded);
            } catch (\JsonException $e) {
                \Log::error('Error decodificando JSON pagos:', ['error' => $e->getMessage(), 'input' => $pagosInput]);
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de pagos inválido: ' . $e->getMessage()
                ], 422);
            }
        }
        
        // Ahora validar
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'caja_id' => 'required|exists:cajas,id',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'nullable|string|max:50',
            'referencia' => 'nullable|string|max:100',
            'voucher' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048',
            'observacion' => 'nullable|string',
            'pagos' => 'required|array|min:1',
            'pagos.*.cronograma_id' => 'required|exists:cronogramas,id',
            'pagos.*.monto' => 'required|numeric|min:0.01',
        ]);

        \Log::info('Datos validados:', $validated);

        DB::beginTransaction();

        try {
            // Subir voucher si existe
            $voucherPath = null;
            if ($request->hasFile('voucher')) {
                $voucherPath = $request->file('voucher')->store('vouchers', 'public');
            }

            $pagosRegistrados = [];

            foreach ($validated['pagos'] as $pagoData) {
                $cronograma = Cronograma::findOrFail($pagoData['cronograma_id']);
                $pagadoActual = $cronograma->pagos->sum('monto_pagado');
                $saldoPendiente = max(0, $cronograma->cuota - $pagadoActual);

                // Validar que el monto no exceda el saldo pendiente
                if ($pagoData['monto'] > $saldoPendiente) {
                    throw new \Exception("El monto para la cuota #{$cronograma->nro_cuota} excede el saldo pendiente de S/ " . number_format($saldoPendiente, 2));
                }

                // Generar referencia única si no se proporciona
                $referencia = $validated['referencia'] ?? 'PAG-' . time() . '-' . $cronograma->id;

                // Crear el pago
                $pago = Pago::create([
                    'cronograma_id' => $pagoData['cronograma_id'],
                    'fecha_pago' => $validated['fecha_pago'],
                    'monto_pagado' => $pagoData['monto'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'referencia' => $referencia,
                    'voucher' => $voucherPath,
                    'observacion' => $validated['observacion'],
                ]);

                $pagosRegistrados[] = $pago;

                // Actualizar estado del cronograma
                $nuevoPagado = $pagadoActual + $pagoData['monto'];
                if ($nuevoPagado >= $cronograma->cuota) {
                    $cronograma->estado = 'pagado';
                    $cronograma->save();
                }

                // Registrar en tesorería
                \App\Http\Controllers\TesoreriaController::registrarIngresoVenta(
                    ventaId: $cronograma->venta_id,
                    cajaId: $validated['caja_id'],
                    monto: $pagoData['monto'],
                    fecha: $validated['fecha_pago'],
                    referencia: $referencia
                );

                // Actualizar estado de la venta si está finalizada
                $venta = $cronograma->venta;
                if ($venta && method_exists($venta, 'isFinalizada') && $venta->isFinalizada()) {
                    $venta->estado = 'finalizado';
                    $venta->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pagos agrupados registrados correctamente. ' . 
                            (count($pagosRegistrados) > 1 ? 
                            'Se procesaron ' . count($pagosRegistrados) . ' cuotas.' : 
                            'Se procesó 1 cuota.'),
                'pagos_count' => count($pagosRegistrados),
                'next_cuotas' => $this->getNextPendingCuotas($validated['cliente_id'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en store pagos agrupados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar los pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método auxiliar para obtener próximas cuotas pendientes
    private function getNextPendingCuotas($clienteId)
    {
        $cliente = Cliente::find($clienteId);
        $nextCuotas = [];
        
        foreach ($cliente->ventas as $venta) {
            $proximaCuota = $venta->cronogramas()
                ->whereIn('estado', ['pendiente', 'vencido'])
                ->orderBy('fecha_pago')
                ->orderBy('nro_cuota')
                ->first();
                
            if ($proximaCuota) {
                $nextCuotas[] = [
                    'venta_id' => $venta->id,
                    'lote_codigo' => $venta->lote->codigo ?? 'N/A',
                    'proxima_cuota_nro' => $proximaCuota->nro_cuota,
                    'proxima_cuota_fecha' => $proximaCuota->fecha_pago
                ];
            }
        }
        
        return $nextCuotas;
    }
}