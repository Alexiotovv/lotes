<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cronograma;
use App\Models\Cliente;
use App\Models\MetodoPago;
use App\Models\Lote;
use App\Models\Tasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Empresa;
use Illuminate\Database\QueryException;
use PDOException;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Venta::with(['cliente', 'lote', 'metodopago']);

        // ✅ Filtrar por usuario si no es admin
        if (!auth()->user()->is_admin) {
            $query->where('user_id', auth()->id());
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('created_at', 'like', "%{$search}%")
                ->orWhereHas('cliente', fn($q2) => $q2->where('dni_ruc', 'like', "%{$search}%"))
                ->orWhereHas('cliente', fn($q2) => $q2->where('nombre_cliente', 'like', "%{$search}%"))
                ->orWhereHas('lote', fn($q2) => $q2->where('codigo', 'like', "%{$search}%"));
                
            });
        }

        $ventas = $query->latest()->paginate(10); 

        return view('ventas.index', compact('ventas', 'search'));
    }
   
    public function create()
    {
        $clientes = Cliente::all();
        $metodos = MetodoPago::all();
        $lotes = Lote::whereIn('estado_lote_id', [1, 2])->get();
        $tasas = Tasa::orderBy('monto')->get();
        return view('ventas.create', compact('clientes', 'metodos', 'lotes','tasas'));
    }

    public function store(Request $request)
    {
        // Validación base
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'metodopago_id' => 'required|exists:metodopagos,id',
            'tasa_interes' => 'required|numeric|min:0|max:1',
            'fecha_pago' => 'required|date',
            'inicial' => 'required|numeric|min:0',
            'numero_cuotas' => 'nullable|integer|min:0', // permitir null o 0+
            'detalles' => 'required|array|min:1',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
        ]);

        // Obtener el método de pago seleccionado
        $metodoPago = \App\Models\MetodoPago::findOrFail($request->metodopago_id);
        $esCredito = $metodoPago->es_credito;

        // Validar número de cuotas según el tipo de venta
        $numeroCuotas = $request->input('numero_cuotas');

        if ($esCredito) {
            // Venta al crédito: debe tener al menos 1 cuota
            if ($numeroCuotas === null || $numeroCuotas < 1) {
                return back()->withErrors([
                    'numero_cuotas' => 'Para ventas al crédito, el número de cuotas debe ser al menos 1.'
                ])->withInput();
            }
        } else {
            // Venta al contado: debe tener 0 cuotas
            if ($numeroCuotas !== null && $numeroCuotas != 0) {
                return back()->withErrors([
                    'numero_cuotas' => 'Para ventas al contado, el número de cuotas debe ser 0.'
                ])->withInput();
            }
        }

        // Validar que el inicial no exceda el precio del lote (opcional pero recomendado)
        if (!empty($request->detalles)) {
            $primerLoteId = $request->detalles[0]['lote_id'];
            $lote = \App\Models\Lote::find($primerLoteId);
            if ($lote) {
                $precioTotal = $lote->area_m2 * $lote->precio_m2;
                if ($request->inicial > $precioTotal) {
                    return back()->withErrors([
                        'inicial' => 'El monto inicial no puede ser mayor al precio total del lote.'
                    ])->withInput();
                }
            }
        }

        

        DB::transaction(function () use ($request, $esCredito) {
            foreach ($request->detalles as $detalle) {
                $lote = \App\Models\Lote::findOrFail($detalle['lote_id']);
                $precioTotal = $lote->area_m2 * $lote->precio_m2;
                $inicial = $request->inicial;
                $montoFinanciar = max(0, $precioTotal - $inicial);
                $nCuotas = $request->numero_cuotas ?? 0;
                $tasaInteres = $request->tasa_interes;

                // Calcular cuota solo si aplica
                $cuota = 0;
                if ($esCredito && $montoFinanciar > 0 && $nCuotas > 0) {
                    if ($tasaInteres > 0) {
                        $tem = pow(1 + $tasaInteres, 1 / 12) - 1;
                        $cuota = ($montoFinanciar * $tem * pow(1 + $tem, $nCuotas)) / (pow(1 + $tem, $nCuotas) - 1);
                    } else {
                        $cuota = $montoFinanciar / $nCuotas;
                    }
                    // ✅ REDONDEAR A ENTERO
                    $cuota = ceil($cuota);
                }

                $venta = \App\Models\Venta::create([
                    'user_id' => auth()->id(),
                    'cliente_id' => $request->cliente_id,
                    'lote_id' => $detalle['lote_id'],
                    'metodopago_id' => $request->metodopago_id,
                    'fecha_pago' => $request->fecha_pago,
                    'numero_cuotas' => $nCuotas,
                    'inicial' => $inicial,
                    'monto_financiar' => round($montoFinanciar, 2),
                    'tasa_interes' => $tasaInteres,
                    'cuota' => $cuota, // ✅ Ya está redondeado a entero
                    'observaciones' => $detalle['observaciones'] ?? null,
                    'cronograma_generado' => false,
                    'estado' => $esCredito ? 'vigente' : 'cerrado',
                ]);

                // Marcar lote como vendido
                $lote->update(['estado_lote_id' => 3]);

                // Registrar inicial en caja si aplica
                if ($inicial > 0) {
                    $cajaPrincipal = \App\Models\Caja::where('nombre', 'Caja Principal')->first()
                        ?? \App\Models\Caja::where('activo', true)->first();

                    if ($cajaPrincipal) {
                        \App\Http\Controllers\TesoreriaController::registrarIngresoVenta(
                            ventaId: $venta->id,
                            cajaId: $cajaPrincipal->id,
                            monto: $inicial,
                            fecha: $request->fecha_pago,
                            conceptoId: \App\Models\Concepto::where('nombre', 'Pago inicial')->value('id')
                                ?? \App\Models\Concepto::where('tipo', 'ingreso')->first()?->id,
                            referencia: 'Inicial Venta #' . $venta->id
                        );
                    }
                }

               
            }
        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
    }

    public function generarCronograma(Venta $venta)
    {
        // dd("test");
        if ($venta->cronograma_generado) {
            return back()->with('warning', 'El cronograma ya fue generado.');
        }

        DB::transaction(function() use ($venta) {
            $saldo = $venta->monto_financiar;
            $tem = $venta->tasa_interes > 0 ? pow(1 + $venta->tasa_interes, 1/12) - 1 : 0;
            $fechaPago = Carbon::parse($venta->fecha_pago);
            $cuota = $venta->cuota;
            $nCuotas = $venta->numero_cuotas;

            for ($i = 1; $i <= $nCuotas; $i++) {
                $interes = $saldo * $tem;
                $amortizacion = $cuota - $interes;
                if ($amortizacion > $saldo) {
                    $amortizacion = $saldo;
                    $cuota = $interes + $amortizacion;
                }

                Cronograma::create([
                    'venta_id' => $venta->id,
                    'nro_cuota' => $i,
                    'fecha_pago' => $fechaPago->copy()->addMonths($i - 1),
                    'saldo' => round($saldo, 2),
                    'interes' => round($interes, 2),
                    'amortizacion' => round($amortizacion, 2),
                    'cuota' => round($cuota, 2),
                    'estado' => 'pendiente',
                ]);

                $saldo = max(0, $saldo - $amortizacion);
            }

            $venta->update(['cronograma_generado' => true]);
        });

        return back()->with('success', 'Cronograma generado correctamente.');
    }


    public function update(Request $request, Venta $venta)
    {
        if ($venta->cronograma_generado) {
            return redirect()->route('ventas.index')->with('error', 'No se puede editar una venta con cronograma generado.');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'metodopago_id' => 'required|exists:metodopagos,id',
            'tasa_interes' => 'required|numeric|min:0|max:1',
            'fecha_pago' => 'required|date',
            'numero_cuotas' => 'required|integer|min:1',
            'inicial' => 'required|numeric|min:0',
            'lote_id' => 'required|exists:lotes,id',
            'observaciones' => 'nullable|string|max:250',
        ]);

        // Verificar que el nuevo lote no esté vendido (excepto si es el mismo)
        if ($venta->lote_id != $validated['lote_id']) {
            $nuevoLote = Lote::findOrFail($validated['lote_id']);
            if ($nuevoLote->estado_lote_id != 1) {
                return back()->withErrors(['lote_id' => 'El lote seleccionado no está disponible.']);
            }
        }

        DB::transaction(function() use ($venta, $validated) {
            $loteNuevo = Lote::findOrFail($validated['lote_id']);
            $precioTotal = $loteNuevo->area_m2 * $loteNuevo->precio_m2;
            $inicial = $validated['inicial'];
            $montoFinanciar = max(0, $precioTotal - $inicial);
            $nCuotas = $validated['numero_cuotas'];
            $tasaInteres = $validated['tasa_interes'];

            // Calcular cuota exactamente igual que en JavaScript
            $cuota = 0;
            if ($montoFinanciar > 0 && $nCuotas > 0) {
                if ($tasaInteres > 0) {
                    // Calcular TEM exactamente igual que en JavaScript
                    $tem = pow(1 + $tasaInteres, 1/12) - 1;
                    // Fórmula exactamente igual que en JavaScript
                    $cuota = ($montoFinanciar * $tem * pow(1 + $tem, $nCuotas)) / (pow(1 + $tem, $nCuotas) - 1);
                } else {
                    $cuota = $montoFinanciar / $nCuotas;
                }
            }

            // Redondear exactamente igual
            $montoFinanciar = round($montoFinanciar, 2);
            $cuota = round($cuota, 2);

            // ✅ 1. Verificar si el lote cambió ANTES de actualizar
            $loteCambio = ($venta->lote_id != $validated['lote_id']);

            // ✅ 2. Si cambia, revertir el lote anterior a "Disponible"
            if ($loteCambio) {
                $loteAnterior = Lote::find($venta->lote_id);
                if ($loteAnterior) {
                    $loteAnterior->update(['estado_lote_id' => 1]); // Disponible
                }
            }

            // ✅ 3. Actualizar la venta
            $venta->update([
                'cliente_id' => $validated['cliente_id'],
                'lote_id' => $validated['lote_id'],
                'metodopago_id' => $validated['metodopago_id'],
                'fecha_pago' => $validated['fecha_pago'],
                'numero_cuotas' => $nCuotas,
                'inicial' => $inicial,
                'monto_financiar' => $montoFinanciar,
                'tasa_interes' => $tasaInteres,
                'cuota' => $cuota,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // ✅ 4. Marcar el NUEVO lote como "Vendido"
            if ($loteCambio) {
                $loteNuevo->update(['estado_lote_id' => 3]); // Vendido
            }
        });

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente.');
    }
    

    public function edit(Venta $venta)
    {
        if ($venta->cronograma_generado) {
            return redirect()->route('ventas.index')->with('error', 'No se puede editar una venta con cronograma generado.');
        }

        $clientes = Cliente::all();
        $metodos = Metodopago::all();

        // Obtener lotes disponibles + el lote de la venta actual (aunque esté vendido)
        $lotesDisponibles = Lote::where('estado_lote_id', 1)->get();
        $loteVenta = Lote::where('id', $venta->lote_id)->first();

        // Combinar: primero el lote de la venta, luego los disponibles
        $lotes = collect();
        if ($loteVenta) {
            $lotes->push($loteVenta);
        }
        $lotes = $lotes->merge($lotesDisponibles)->unique('id');

        return view('ventas.edit', compact('venta', 'clientes', 'metodos', 'lotes'));
    }

    public function cronograma(Venta $venta)
    {
        // Trae los registros del cronograma asociados a esta venta
        $rows = Cronograma::where('venta_id', $venta->id)
            ->orderBy('nro_cuota')
            ->get();

        // Obtener totales
        $totalInteres = $rows->sum('interes');
        $totalAmortizacion = $rows->sum('amortizacion');
        $totalCuota = $rows->sum('cuota');
        $totalAPagar = $venta->inicial + $totalCuota;

        // Formatear totales
        $totalInteresFormateado = number_format($totalInteres, 2, '.', ',');
        $totalAmortizacionFormateado = number_format($totalAmortizacion, 2, '.', ',');
        $totalAPagarFormateado = number_format($totalAPagar, 2, '.', ',');

        // Información de empresa
        $empresa = Empresa::first();

        return view('ventas.cronograma', compact(
            'venta',
            'rows',
            'empresa',
            'totalInteresFormateado',
            'totalAmortizacionFormateado',
            'totalAPagarFormateado'
        ));
    }

    public function destroy($id)
    {
        $venta = Venta::with('lote')->findOrFail($id);

        try {
            DB::transaction(function () use ($venta) {
                // 1. Eliminar movimientos asociados
                \App\Models\Movimiento::where('venta_id', $venta->id)->delete();

                // 2. Revertir el lote a estado "Disponible"
                if ($venta->lote) {
                    $venta->lote->update(['estado_lote_id' => 1]);
                }

                // 3. Eliminar la venta
                $venta->delete();
            });

            return redirect()->route('ventas.index')
                ->with('success', 'Venta, sus movimientos y lote actualizados correctamente.');

        } catch (QueryException $e) {
            // ✅ Detectar si es un error de restricción de clave foránea
            if ($e->getCode() === '23000') { // Código de error de integridad referencial
                $mensaje = 'No se puede eliminar la venta porque tiene registros asociados (como contratos, cronogramas, movimientos, etc).';
                return redirect()->route('ventas.index')->with('error', $mensaje);
            }

            // ✅ Otro error de base de datos
            \Log::error('Error al eliminar venta: ' . $e->getMessage());
            return redirect()->route('ventas.index')->with('error', 'Ocurrió un error al intentar eliminar la venta.');
        } catch (\Exception $e) {
            // ✅ Error general (no relacionado con la base de datos)
            \Log::error('Error inesperado al eliminar venta: ' . $e->getMessage());
            return redirect()->route('ventas.index')->with('error', 'Ocurrió un error inesperado.');
        }
    }
    

    public function cambiarEstado(Request $request, Venta $venta)
    {
        if (!auth()->user()->is_admin) {
            return back()->withErrors(['error' => 'No tiene permiso para cambiar el estado.']);
        }

        $request->validate([
            'estado' => 'required|in:vigente,finalizado,desistido'
        ]);

        // if ($venta->estado === 'desistido') {
        //     return back()->withErrors(['estado' => 'No se puede modificar el estado de una venta desistida.']);
        // }

        // Si el nuevo estado es "desistido", revertir el lote a "Disponible"
        if ($request->estado === 'desistido') {
            $venta->lote()->update(['estado_lote_id' => 1]); // Disponible
        }
        // Si el nuevo estado es "vigente" o "finalizado", actualizar el lote a "Vendido"
        elseif (in_array($request->estado, ['vigente', 'finalizado'])) {
            $venta->lote()->update(['estado_lote_id' => 3]); // Vendido
        }

        $venta->update(['estado' => $request->estado]);

        return redirect()->route('ventas.index')->with('success', 'Estado actualizado correctamente.');
    }

    public function detalleCronograma(Venta $venta)
    {
        $cronogramas = $venta->cronogramas()->orderBy('nro_cuota')->get();

        // Verificar si tiene pagos
        $tienePagos = false;
        $esAgrupado = false;
        $grupoId = null;
        
        foreach ($cronogramas as $crono) {
            if ($crono->pagos()->exists()) {
                $tienePagos = true;
            }
            // Verificar si tiene grupo_id (está agrupado)
            if (!empty($crono->grupo_id)) {
                $esAgrupado = true;
                $grupoId = $crono->grupo_id;
                break;
            }
        }

        return response()->json([
            'cronogramas' => $cronogramas->map(function ($c) {
                return [
                    'id' => $c->id,
                    'nro_cuota' => $c->nro_cuota,
                    'fecha_pago' => $c->fecha_pago->format('d/m/Y'),
                    'cuota' => $c->cuota,
                    'saldo' => $c->saldo,
                    'interes' => $c->interes,
                    'amortizacion' => $c->amortizacion,
                    'estado' => $c->estado,
                    'grupo_id' => $c->grupo_id, // ← NUEVO
                ];
            }),
            'tiene_pagos' => $tienePagos,
            'es_agrupado' => $esAgrupado, // ← NUEVO
            'grupo_id' => $grupoId, // ← NUEVO
        ]);
    }

    public function eliminarCronograma(Venta $venta)
    {
        // ✅ Verificar si tiene pagos
        foreach ($venta->cronogramas as $crono) {
            if ($crono->pagos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cronograma porque tiene pagos registrados.'
                ]);
            }
        }

        DB::transaction(function () use ($venta) {
            // ✅ Eliminar cronogramas
            $venta->cronogramas()->delete();

            // ✅ Actualizar campo cronograma_generado
            $venta->update([
                'cronograma_generado' => false, // ✅ Nuevo
            ]);

          
        });

        return response()->json([
            'success' => true,
            'message' => 'Cronograma eliminado correctamente.'
        ]);
    }


    public function actualizarFechaCronograma(Request $request, Venta $venta)
    {
        // Verificar que la venta tenga cronograma
        if (!$venta->cronograma_generado) {
            return response()->json([
                'success' => false,
                'message' => 'La venta no tiene cronograma generado.'
            ], 400);
        }

        // Verificar que el cronograma no esté agrupado
        if ($venta->cronogramas()->whereNotNull('grupo_id')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede modificar un cronograma agrupado desde aquí.'
            ], 400);
        }

        $request->validate([
            'nueva_fecha' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            // Obtener cronogramas ordenados
            $cronogramas = $venta->cronogramas()->orderBy('nro_cuota')->get();
            
            if ($cronogramas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron cronogramas para esta venta.'
                ], 404);
            }

            $nuevaFechaPrimerPago = Carbon::parse($request->nueva_fecha);
            
            // Cambiar TODAS las fechas del cronograma
            foreach ($cronogramas as $cronograma) {
                $nuevaFecha = $nuevaFechaPrimerPago->copy()->addMonths($cronograma->nro_cuota - 1);
                $cronograma->update([
                    'fecha_pago' => $nuevaFecha
                ]);
            }

            // Actualizar la fecha en la venta también
            $venta->update([
                'fecha_pago' => $nuevaFechaPrimerPago
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fechas del cronograma actualizadas correctamente.',
                'nueva_fecha_inicio' => $nuevaFechaPrimerPago->format('d/m/Y')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las fechas: ' . $e->getMessage()
            ], 500);
        }
    }


    // Obtener TODOS los propietarios (titular + adicionales) de una venta
    public function getPropietariosAdicionales(Venta $venta)
    {
        // Obtener el cliente principal (titular)
        $titular = [
            'id' => $venta->cliente->id,
            'nombre_cliente' => $venta->cliente->nombre_cliente,
            'dni_ruc' => $venta->cliente->dni_ruc,
            'es_titular' => true,
            'titular_text' => ' (Titular)'
        ];
        
        // Obtener propietarios adicionales
        $propietariosAdicionales = $venta->clientesAdicionales()->get()
            ->map(function ($cliente) {
                return [
                    'id' => $cliente->id,
                    'nombre_cliente' => $cliente->nombre_cliente,
                    'dni_ruc' => $cliente->dni_ruc,
                    'es_titular' => false,
                    'titular_text' => ' (Adicional)'
                ];
            });
        
        // Combinar titular + adicionales
        $todosPropietarios = collect([$titular])->merge($propietariosAdicionales);
        
        return response()->json($todosPropietarios);
    }

    // Agregar propietario adicional
    public function agregarPropietario(Request $request, Venta $venta)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id'
        ]);

        // Verificar que el cliente no sea el titular principal
        if ($venta->cliente_id == $request->cliente_id) {
            return response()->json([
                'success' => false,
                'message' => 'Este cliente ya es el titular principal de la venta.'
            ], 400);
        }

        // Verificar que no esté ya agregado
        if ($venta->clientesAdicionales()->where('cliente_id', $request->cliente_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este cliente ya está registrado como propietario adicional.'
            ], 400);
        }

        // Agregar propietario
        $venta->clientesAdicionales()->attach($request->cliente_id);

        return response()->json([
            'success' => true,
            'message' => 'Propietario adicional agregado correctamente.'
        ]);
    }


}
