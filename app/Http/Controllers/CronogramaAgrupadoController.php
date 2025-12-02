<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Cronograma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CronogramaAgrupadoController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::with(['ventas' => function($q) {
            $q->where('ventas.estado', 'vigente')
            ->whereHas('metodopago', function($q2) {
                $q2->where('es_credito', true);
            })
            ->where('cronograma_generado', false);
        }])->whereHas('ventas', function($q) {
            $q->where('ventas.estado', 'vigente')
            ->whereHas('metodopago', function($q2) {
                $q2->where('es_credito', true);
            })
            ->where('cronograma_generado', false);
        });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_cliente', 'LIKE', "%$search%")
                ->orWhere('dni_ruc', 'LIKE', "%$search%");
            });
        }

        $clientes = $query->latest()->paginate(15)->appends($request->query());

        return view('cronogramas-agrupados.index', compact('clientes'));
    }

    public function getVentasCliente(Cliente $cliente)
    {
        $ventas = $cliente->ventas()
            ->with(['lote', 'metodopago'])
            ->where('ventas.estado', 'vigente')
            ->whereHas('metodopago', function($q) {
                $q->where('es_credito', true);
            })
            ->where('cronograma_generado', false)
            ->get()
            ->map(function($venta) {
                // ✅ Formatear la fecha a YYYY-MM-DD para input type="date"
                $fechaFormateada = $venta->fecha_pago ? \Carbon\Carbon::parse($venta->fecha_pago)->format('Y-m-d') : null;
                
                return [
                    'id' => $venta->id,
                    'lote_codigo' => $venta->lote->codigo ?? 'N/A',
                    'lote_nombre' => $venta->lote->nombre ?? 'N/A',
                    'precio_total' => $venta->lote ? ($venta->lote->area_m2 * $venta->lote->precio_m2) : 0,
                    'inicial' => $venta->inicial,
                    'saldo_pendiente' => $venta->monto_financiar,
                    'numero_cuotas' => $venta->numero_cuotas,
                    'tasa_interes' => $venta->tasa_interes,
                    'fecha_primer_pago' => $fechaFormateada, // ✅ Ya formateada
                    'cuota_actual' => $venta->cuota
                ];
            });

        return response()->json($ventas);
    }

    public function generar(Request $request)
{
    $validated = $request->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'ventas' => 'required|array|min:1',
        'ventas.*.id' => 'required|exists:ventas,id',
        'numero_cuotas' => 'required|integer|min:1|max:360',
        'fecha_primer_pago' => 'required|date',
        'tasa_interes' => 'required|numeric|min:0|max:1',
    ]);

    DB::beginTransaction();

    try {
        $cliente = Cliente::findOrFail($validated['cliente_id']);
        
        // Obtener ventas con sus datos ORIGINALES
        $ventasData = [];
        $totalFinanciar = 0;
        
        foreach ($validated['ventas'] as $ventaData) {
            $venta = Venta::findOrFail($ventaData['id']);
            
            // ✅ USAR DATOS ORIGINALES DE LA VENTA
            $ventasData[] = [
                'venta' => $venta,
                'monto_financiar' => $venta->monto_financiar, // Original
                'cuota_original' => $venta->cuota, // Original
                'numero_cuotas' => $venta->numero_cuotas, // Original
                'tasa_interes' => $venta->tasa_interes, // Original
            ];
            
            $totalFinanciar += $venta->monto_financiar;
        }
        
        // Calcular cuota mensual TOTAL (redondeada)
        $numeroCuotas = $validated['numero_cuotas'];
        $tasaInteres = $validated['tasa_interes'];
        $tem = $tasaInteres > 0 ? pow(1 + $tasaInteres, 1/12) - 1 : 0;
        $cuotaMensualTotal = 0;
        
        if ($totalFinanciar > 0 && $numeroCuotas > 0) {
            if ($tasaInteres > 0) {
                $cuotaMensualTotal = ($totalFinanciar * $tem * pow(1 + $tem, $numeroCuotas)) / (pow(1 + $tem, $numeroCuotas) - 1);
            } else {
                $cuotaMensualTotal = $totalFinanciar / $numeroCuotas;
            }
            // ✅ REDONDEAR AL ENTERO
            $cuotaMensualTotal = round($cuotaMensualTotal);
        }
        
        // ✅ CREAR UN IDENTIFICADOR ÚNICO PARA EL GRUPO DE CRONOGRAMAS
        $grupoId = 'GRP-' . time() . '-' . $cliente->id;
        
        // ✅ CALCULAR PROPORCIONES Y REDONDEAR
        $proporciones = [];
        $cuotasPorVenta = [];
        
        foreach ($ventasData as $item) {
            $proporcion = $item['monto_financiar'] / $totalFinanciar;
            $cuotaVenta = $cuotaMensualTotal * $proporcion;
            $proporciones[] = $proporcion;
            $cuotasPorVenta[] = $cuotaVenta;
        }
        
        // ✅ REDONDEAR DISTRIBUCIÓN MANTENIENDO EL TOTAL
        $cuotasRedondeadas = \App\Helpers\CalculosHelper::redondearDistribucion($cuotasPorVenta);
        
        // Generar cronogramas para cada venta con el mismo grupo_id
        $fechaPago = Carbon::parse($validated['fecha_primer_pago']);
        
        for ($i = 1; $i <= $numeroCuotas; $i++) {
            foreach ($ventasData as $index => $item) {
                $venta = $item['venta'];
                $montoFinanciar = $item['monto_financiar'];
                $cuotaVenta = $cuotasRedondeadas[$index];
                
                // Calcular para cada cuota
                $saldoVenta = $montoFinanciar;
                $interesVenta = 0;
                $amortizacionVenta = $cuotaVenta;
                
                // Si hay interés, calcularlo
                if ($item['tasa_interes'] > 0) {
                    $temVenta = pow(1 + $item['tasa_interes'], 1/12) - 1;
                    
                    // Recalcular para cada cuota
                    $saldoVenta = max(0, $montoFinanciar - (($i-1) * $cuotaVenta));
                    $interesVenta = $saldoVenta * $temVenta;
                    $amortizacionVenta = $cuotaVenta - $interesVenta;
                    
                    // Asegurar que la amortización no sea negativa
                    if ($amortizacionVenta < 0) {
                        $amortizacionVenta = 0;
                        $cuotaVenta = $interesVenta;
                    }
                    
                    // Asegurar que no exceda el saldo
                    if ($amortizacionVenta > $saldoVenta) {
                        $amortizacionVenta = $saldoVenta;
                        $cuotaVenta = $interesVenta + $amortizacionVenta;
                    }
                }
                
                // ✅ REDONDEAR TODOS LOS MONTOS AL ENTERO
                $saldoVenta = round($saldoVenta);
                $interesVenta = round($interesVenta);
                $amortizacionVenta = round($amortizacionVenta);
                $cuotaVenta = round($cuotaVenta);
                
                Cronograma::create([
                    'venta_id' => $venta->id,
                    'grupo_id' => $grupoId,
                    'nro_cuota' => $i,
                    'fecha_pago' => $fechaPago->copy()->addMonths($i - 1),
                    'saldo' => $saldoVenta,
                    'interes' => $interesVenta,
                    'amortizacion' => $amortizacionVenta,
                    'cuota' => $cuotaVenta,
                    'estado' => 'pendiente',
                ]);
            }
        }
        
        // ✅ SOLO MARCAR COMO GENERADO (NO MODIFICAR OTROS CAMPOS)
        foreach ($ventasData as $item) {
            $item['venta']->update([
                'cronograma_generado' => true
            ]);
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Cronograma agrupado generado correctamente para ' . count($ventasData) . ' ventas.',
            'grupo_id' => $grupoId,
            'cuota_total_redondeada' => $cuotaMensualTotal,
            'detalle_cuotas' => $cuotasRedondeadas
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al generar cronograma agrupado: ' . $e->getMessage()
        ], 500);
    }
}

    public function verCronogramaAgrupado($grupoId)
    {
        // Obtener todos los cronogramas del grupo
        $cronogramas = Cronograma::where('grupo_id', $grupoId)
            ->orderBy('nro_cuota')
            ->orderBy('venta_id')
            ->get();
            
        if ($cronogramas->isEmpty()) {
            abort(404, 'Cronograma agrupado no encontrado');
        }
        
        // Obtener la primera venta para información del cliente
        $primerCronograma = $cronogramas->first();
        $venta = $primerCronograma->venta()->with('cliente')->first();
        $cliente = $venta->cliente;
        
        // Obtener todas las ventas del grupo
        $ventaIds = $cronogramas->pluck('venta_id')->unique();
        $ventas = Venta::whereIn('id', $ventaIds)->with('lote')->get();
        
        // ✅ AGRUPAR CRONOGRAMAS POR NÚMERO DE CUOTA
        $cronogramasAgrupados = [];
        foreach ($cronogramas->groupBy('nro_cuota') as $nroCuota => $cuotas) {
            $cronogramasAgrupados[] = [
                'nro_cuota' => $nroCuota,
                'fecha_pago' => $cuotas->first()->fecha_pago,
                'saldo_total' => $cuotas->sum('saldo'),
                'interes_total' => $cuotas->sum('interes'),
                'amortizacion_total' => $cuotas->sum('amortizacion'),
                'cuota_total' => $cuotas->sum('cuota'),
            ];
        }
        
        // Calcular totales
        $totalInteres = $cronogramas->sum('interes');
        $totalAmortizacion = $cronogramas->sum('amortizacion');
        $totalCuota = $cronogramas->sum('cuota');
        
        $totalInicial = $ventas->sum('inicial');
        $totalFinanciar = $ventas->sum('monto_financiar');
        $totalPagar = $totalInicial + $totalCuota;
        
        // Obtener empresa
        $empresa = \App\Models\Empresa::first() ?? (object) [
            'nombre' => 'CONSTRUCCIONES E INMOBILIARIA ALARCON SAC',
            'ruc' => '20603441568',
            'direccion' => 'PSJ. SIMÓN BOLÍVAR N° 159 - MORALES',
            'descripcion' => 'LOTIZACIÓN LOS CEDROS DE SAN JUAN',
            'logo' => null
        ];

        return view('cronogramas-agrupados.cronograma-agrupado', compact(
            'empresa',
            'cliente',
            'ventas',
            'cronogramasAgrupados',
            'totalInteres',
            'totalAmortizacion',
            'totalCuota',
            'totalInicial',
            'totalFinanciar',
            'totalPagar',
            'grupoId'
        ));
    }

    public function impresiones(Request $request)
    {
        // Buscar clientes que tienen cronogramas agrupados (con grupo_id)
        $query = Cliente::whereHas('ventas.cronogramas', function($q) {
            $q->whereNotNull('grupo_id');
        });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_cliente', 'LIKE', "%$search%")
                ->orWhere('dni_ruc', 'LIKE', "%$search%");
            });
        }

        $clientes = $query->latest()->paginate(15)->appends($request->query());

        return view('cronogramas-agrupados.impresiones', compact('clientes'));
    }

    public function getGruposCliente(Cliente $cliente)
    {
        // Obtener grupos únicos de cronogramas del cliente
        $grupos = Cronograma::whereHas('venta', function($q) use ($cliente) {
                $q->where('cliente_id', $cliente->id);
            })
            ->whereNotNull('grupo_id')
            ->select('grupo_id')
            ->distinct()
            ->get()
            ->pluck('grupo_id');
        
        $resultados = [];
        
        foreach ($grupos as $grupoId) {
            if (!$grupoId) continue;
            
            // Obtener todos los cronogramas de este grupo
            $cronogramas = Cronograma::where('grupo_id', $grupoId)
                ->orderBy('nro_cuota')
                ->get();
            
            if ($cronogramas->isEmpty()) continue;
            
            // Obtener la primera cuota para información
            $primerCronograma = $cronogramas->first();
            
            // Obtener ventas del grupo
            $ventaIds = $cronogramas->pluck('venta_id')->unique();
            $ventas = Venta::whereIn('id', $ventaIds)->with('lote')->get();
            
            // Calcular información del grupo
            $resultados[] = [
                'grupo_id' => $grupoId,
                'fecha_generacion' => $primerCronograma->created_at->format('d/m/Y H:i'),
                'nro_cuotas' => $cronogramas->max('nro_cuota'),
                'cuota_mensual_total' => $cronogramas->where('nro_cuota', 1)->sum('cuota'),
                'total_financiado' => $cronogramas->where('nro_cuota', 1)->sum('saldo'),
                'lotes' => $ventas->map(function($venta) {
                    return $venta->lote->codigo ?? 'N/A';
                })->implode(', '),
                'ventas_ids' => $ventaIds->implode(','),
                'estado' => $this->determinarEstadoGrupo($cronogramas)
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }

    private function determinarEstadoGrupo($cronogramas)
    {
        $pagadas = $cronogramas->where('estado', 'pagado')->count();
        $total = $cronogramas->count();
        
        if ($pagadas === 0) {
            return 'pendiente';
        } elseif ($pagadas === $total) {
            return 'pagado';
        } elseif ($cronogramas->where('estado', 'vencido')->count() > 0) {
            return 'vencido';
        } else {
            return 'parcial';
        }
    }

    public function eliminarGrupo($grupoId)
    {
        DB::beginTransaction();
        
        try {
            // Obtener cronogramas del grupo
            $cronogramas = Cronograma::where('grupo_id', $grupoId)->get();
            
            // Verificar si tienen pagos
            foreach ($cronogramas as $cronograma) {
                if ($cronograma->pagos()->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el cronograma porque tiene pagos registrados.'
                    ]);
                }
            }
            
            // Obtener ventas del grupo
            $ventaIds = $cronogramas->pluck('venta_id')->unique();
            
            // Eliminar cronogramas del grupo
            Cronograma::where('grupo_id', $grupoId)->delete();
            
            // Actualizar ventas
            Venta::whereIn('id', $ventaIds)->update([
                'cronograma_generado' => false,
                'numero_cuotas' => 0,
                'cuota' => 0
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Grupo de cronogramas eliminado correctamente.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar grupo: ' . $e->getMessage()
            ], 500);
        }
    }
}