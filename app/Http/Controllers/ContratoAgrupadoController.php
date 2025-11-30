<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Empresa;
use App\Models\Contrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContratoAgrupadoController extends Controller
{
    public function index()
    {
        
        return view('contratos.agrupados.index');
    }

    public function buscarCliente(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3'
        ]);

        $search = $request->search;

        try {
            \Log::info('=== INICIO buscarCliente ===');
            \Log::info('🔍 Término de búsqueda:', ['search' => $search]);

            $clientes = Cliente::where(function($query) use ($search) {
                    $query->where('dni_ruc', 'LIKE', "%{$search}%")
                        ->orWhere('nombre_cliente', 'LIKE', "%{$search}%");
                })
                ->whereHas('ventas', function($query) {
                    $query->where('estado', 'vigente');
                })
                ->withCount(['ventas' => function($query) {
                    $query->where('estado', 'vigente');
                }])
                ->with(['ventas.contratos' => function($query) {
                    $query->where('activo', true);
                }, 'ventas.lote'])
                ->get();

            \Log::info('📋 Clientes encontrados:', ['count' => $clientes->count()]);

            // 🔥 CORRECIÓN: Convertir a array simple para JSON
            $clientesArray = [];
            foreach ($clientes as $cliente) {
                // Calcular contratos_count
                $contratosCount = $cliente->ventas->flatMap(function($venta) {
                    return $venta->contratos;
                })->unique('id')->count();
                
                // Calcular contratos_grouped
                $contratosGrouped = $cliente->ventas->flatMap(function($venta) {
                    return $venta->contratos->map(function($contrato) use ($venta) {
                        return [
                            'contrato' => $contrato,
                            'venta_lote' => $venta->lote->codigo ?? 'N/A',
                            'venta_id' => $venta->id
                        ];
                    });
                })->groupBy(function($item) {
                    return $item['contrato']->contenido_html;
                })->map(function($group) {
                    $firstContrato = $group->first()['contrato'];
                    return [
                        'contrato_id' => $firstContrato->id,
                        'lotes' => $group->pluck('venta_lote')->implode(', '),
                        'fecha' => $firstContrato->created_at->format('d/m/Y H:i'),
                        'ventas_ids' => $group->pluck('venta_id')->implode(',')
                    ];
                })->values()->toArray(); // 🔥 Convertir a array

                $clientesArray[] = [
                    'id' => $cliente->id,
                    'dni_ruc' => $cliente->dni_ruc,
                    'nombre_cliente' => $cliente->nombre_cliente,
                    'genero' => $cliente->genero,
                    'estado_civil' => $cliente->estado_civil,
                    'direccion' => $cliente->direccion,
                    'departamento' => $cliente->departamento,
                    'provincia' => $cliente->provincia,
                    'distrito' => $cliente->distrito,
                    'telefono' => $cliente->telefono,
                    'created_at' => $cliente->created_at,
                    'updated_at' => $cliente->updated_at,
                    'ventas_count' => $cliente->ventas_count,
                    'contratos_count' => $contratosCount,
                    'contratos_grouped' => $contratosGrouped
                ];
            }

            \Log::info('✅ Respuesta final preparada');

            return response()->json([
                'success' => true,
                'data' => $clientesArray, // 🔥 Usar array simple
                'count' => count($clientesArray)
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERROR en buscarCliente', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    // public function buscarCliente(Request $request)
    // {
    //     $request->validate([
    //         'search' => 'required|string|min:3'
    //     ]);

    //     $search = $request->search;

    //     try {
    //         $clientes = Cliente::where(function($query) use ($search) {
    //                 $query->where('dni_ruc', 'LIKE', "%{$search}%")
    //                     ->orWhere('nombre_cliente', 'LIKE', "%{$search}%");
    //             })
    //             ->whereHas('ventas', function($query) {
    //                 $query->where('estado', 'vigente');
    //             })
    //             ->withCount(['ventas' => function($query) {
    //                 $query->where('estado', 'vigente');
    //             }])
    //             ->with(['ventas.contratos' => function($query) {
    //                 $query->where('activo', true);
    //             }, 'ventas.lote'])
    //             ->get();

    //         // 🔥 AGREGAR INFORMACIÓN DE CONTRATOS EXISTENTES - CORREGIDO
    //         $clientesConContratos = $clientes->map(function($cliente) {
    //             // Calcular contratos_count
    //             $contratosCount = $cliente->ventas->flatMap(function($venta) {
    //                 return $venta->contratos;
    //             })->unique('id')->count();
                
    //             // Calcular contratos_grouped
    //             $contratosGrouped = $cliente->ventas->flatMap(function($venta) {
    //                 return $venta->contratos->map(function($contrato) use ($venta) {
    //                     return [
    //                         'contrato' => $contrato,
    //                         'venta_lote' => $venta->lote->codigo ?? 'N/A',
    //                         'venta_id' => $venta->id
    //                     ];
    //                 });
    //             })->groupBy(function($item) {
    //                 return $item['contrato']->contenido_html;
    //             })->map(function($group) {
    //                 $firstContrato = $group->first()['contrato'];
    //                 return [
    //                     'contrato_id' => $firstContrato->id,
    //                     'lotes' => $group->pluck('venta_lote')->implode(', '),
    //                     'fecha' => $firstContrato->created_at->format('d/m/Y H:i'),
    //                     'ventas_ids' => $group->pluck('venta_id')->implode(',')
    //                 ];
    //             })->values();

    //             // Devolver cliente con la información adicional
    //             return [
    //                 'id' => $cliente->id,
    //                 'dni_ruc' => $cliente->dni_ruc,
    //                 'nombre_cliente' => $cliente->nombre_cliente,
    //                 'genero' => $cliente->genero,
    //                 'estado_civil' => $cliente->estado_civil,
    //                 'direccion' => $cliente->direccion,
    //                 'departamento' => $cliente->departamento,
    //                 'provincia' => $cliente->provincia,
    //                 'distrito' => $cliente->distrito,
    //                 'telefono' => $cliente->telefono,
    //                 'created_at' => $cliente->created_at,
    //                 'updated_at' => $cliente->updated_at,
    //                 'ventas_count' => $cliente->ventas_count,
    //                 'contratos_count' => $contratosCount,
    //                 'contratos_grouped' => $contratosGrouped
    //             ];
    //         });

    //         \Log::info('Clientes con contratos:', $clientesConContratos->toArray());

    //         return response()->json([
    //             'success' => true,
    //             'data' => $clientesConContratos,
    //             'count' => $clientesConContratos->count()
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error('Error en buscarCliente', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error en la búsqueda: ' . $e->getMessage(),
    //             'data' => []
    //         ], 500);
    //     }
    // }

    public function getVentasCliente($clienteId)
    {
        try {
            $ventas = Venta::with(['lote', 'metodopago'])
                ->where('cliente_id', $clienteId)
                ->where('estado', 'vigente')
                ->whereDoesntHave('contratos', function($query) {
                    $query->where('activo', true);
                })
                ->get()
                ->map(function($venta) {
                    return [
                        'id' => $venta->id,
                        'lote_codigo' => $venta->lote->codigo,
                        'lote_nombre' => $venta->lote->nombre,
                        'area_m2' => $venta->lote->area_m2,
                        'precio_total' => $venta->lote->area_m2 * $venta->lote->precio_m2,
                        'metodo_pago' => $venta->metodopago->nombre,
                        'numero_cuotas' => $venta->numero_cuotas,
                        'cuota' => $venta->cuota,
                        'inicial' => $venta->inicial,
                        'fecha_pago' => $venta->fecha_pago->format('Y-m-d'),
                        'selected' => false
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $ventas,
                'count' => $ventas->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar ventas: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function generarContrato(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'ventas_seleccionadas' => 'required|array|min:1',
            'ventas_seleccionadas.*' => 'exists:ventas,id'
        ]);

        try {
            DB::beginTransaction();

            $cliente = Cliente::findOrFail($request->cliente_id);
            $ventas = Venta::with(['lote', 'metodopago'])
                ->whereIn('id', $request->ventas_seleccionadas)
                ->where('cliente_id', $request->cliente_id)
                ->where('estado', 'vigente')
                ->get();

            if ($ventas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron ventas válidas para generar el contrato.'
                ], 404);
            }

            $empresa = Empresa::first();
            if (!$empresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró información de la empresa.'
                ], 404);
            }

            // Generar contenido HTML del contrato agrupado
            $contenidoHTML = $this->generarHTMLContratoAgrupado($cliente, $ventas, $empresa);

            // Crear registro de contrato para cada venta
            foreach ($ventas as $venta) {
                Contrato::create([
                    'venta_id' => $venta->id,
                    'user_id' => auth()->id(),
                    'contenido_html' => $contenidoHTML,
                    'activo' => true
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato agrupado generado exitosamente para ' . $ventas->count() . ' lotes.',
                'url' => route('contratos.agrupados.vista-previa', [
                    'cliente_id' => $cliente->id,
                    'ventas' => implode(',', $request->ventas_seleccionadas)
                ])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generarHTMLContratoAgrupado($cliente, $ventas, $empresa)
    {
        // Calcular totales
        $totalLotes = $ventas->count();
        $totalPrecio = $ventas->sum(function($venta) {
            return $venta->lote->area_m2 * $venta->lote->precio_m2;
        });
        $totalInicial = $ventas->sum('inicial');
        $totalFinanciar = $ventas->sum('monto_financiar');
        $totalCuota = $ventas->sum('cuota');

        return view('contratos.plantilla-agrupada', [
            'empresa' => $empresa,
            'cliente' => $cliente,
            'ventas' => $ventas,
            'totalLotes' => $totalLotes,
            'totalPrecio' => $totalPrecio,
            'totalInicial' => $totalInicial,
            'totalFinanciar' => $totalFinanciar,
            'totalCuota' => $totalCuota
        ])->render();
    }

    public function vistaPrevia(Request $request)
    {
        $cliente = Cliente::findOrFail($request->cliente_id);
        $ventasIds = explode(',', $request->ventas);
        
        $ventas = Venta::with(['lote', 'metodopago'])
            ->whereIn('id', $ventasIds)
            ->where('cliente_id', $request->cliente_id)
            ->get();

        $empresa = Empresa::first();

        // 🔥 CALCULAR LOS TOTALES QUE NECESITA LA VISTA
        $totalPrecio = $ventas->sum(function($venta) {
            return $venta->lote->area_m2 * $venta->lote->precio_m2;
        });
        
        $totalLotes = $ventas->count();
        $totalInicial = $ventas->sum('inicial');
        $totalFinanciar = $ventas->sum('monto_financiar');
        $totalCuota = $ventas->sum('cuota');

        return view('contratos.plantilla-agrupada', compact(
            'cliente', 
            'ventas', 
            'empresa',
            'totalPrecio',
            'totalLotes',
            'totalInicial',
            'totalFinanciar',
            'totalCuota'
        ));
    }

    // En el controlador, agrega este método si necesitas más control
    public function getContratosCliente($clienteId)
    {
        try {
            $contratos = Contrato::whereHas('venta', function($query) use ($clienteId) {
                    $query->where('cliente_id', $clienteId);
                })
                ->where('activo', true)
                ->with(['venta.lote'])
                ->get()
                ->groupBy('contenido_html')
                ->map(function($group) {
                    return [
                        'contrato_id' => $group->first()->id,
                        'lotes' => $group->map(function($contrato) {
                            return $contrato->venta->lote->codigo;
                        })->implode(', '),
                        'fecha' => $group->first()->created_at->format('d/m/Y H:i'),
                        'ventas_ids' => $group->pluck('venta_id')->implode(',')
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data' => $contratos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar contratos: ' . $e->getMessage()
            ], 500);
        }
    }


}