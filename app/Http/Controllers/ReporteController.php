<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Cronograma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{



    public function creditosPorCobrarPDF(Request $request)
    {
        $query = Venta::with(['cliente', 'lote', 'cronogramas'])
            ->whereHas('metodoPago', function ($q) {
                $q->where('es_credito', true);
            })
            ->where('estado', 'vigente'); // Solo créditos vigentes

        // Filtros por fecha si se envían
        if ($fecha_desde = $request->input('fecha_desde')) {
            $query->whereDate('created_at', '>=', $fecha_desde);
        }
        if ($fecha_hasta = $request->input('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $fecha_hasta);
        }

        $ventas = $query->get();

        $total_creditos = $ventas->count();
        $monto_total = $ventas->sum(function ($venta) {
            return $venta->monto_financiar - $venta->cronogramas->where('estado', 'pagado')->sum('cuota');
        });

        $empresa = Empresa::first();

        return view('reportes.ventas.pdf.creditos_cobrar', compact(
            'ventas',
            'total_creditos',
            'monto_total',
            'empresa',
            'fecha_desde',
            'fecha_hasta'
        ));
    }

    
    public function creditosClientePorDni(Request $request)
    {
        $dni = $request->query('dni');

        $cliente = Cliente::where('dni_ruc', $dni)->first();

        if (!$cliente) {
            return response()->json(['cliente' => null]);
        }

        $ventas = Venta::where('cliente_id', $cliente->id)
            ->whereHas('metodoPago', function ($q) {
                $q->where('es_credito', true);
            })
            ->with(['lote', 'metodoPago'])
            ->get()
            ->map(function ($venta) {
                return [
                    'id' => $venta->id,
                    'lote' => [
                        'codigo' => $venta->lote->codigo,
                        'nombre' => $venta->lote->nombre,
                    ],
                    'fecha_pago' => $venta->fecha_pago->format('d/m/Y'),
                    'total_venta' => (float)($venta->lote->area_m2 * $venta->lote->precio_m2), // ✅ Convertir a float
                    'cuota' => (float)$venta->cuota, // ✅ Convertir a float
                    'numero_cuotas' => $venta->numero_cuotas,
                    'estado' => $venta->estado,
                ];
            });

        return response()->json([
            'cliente' => $cliente,
            'creditos' => $ventas,
        ]);
    }

    public function detallesCredito(Venta $venta)
    {
        // ✅ Verificar que la venta exista
        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada'], 404);
        }

        try {
            $cronogramas = Cronograma::where('venta_id', $venta->id)
                ->with(['pagos']) // Cargar los pagos de cada cronograma
                ->get()
                ->map(function ($crono) {
                    $pagado = $crono->pagos->sum('monto_pagado');
                    $saldo = $crono->cuota - $pagado;

                    return [
                        'nro_cuota' => $crono->nro_cuota,
                        'fecha_pago' => $crono->fecha_pago->format('d/m/Y'),
                        'cuota' => (float)$crono->cuota,
                        'pagado' => (float)$pagado,
                        'saldo' => (float)$saldo,
                        'estado' => $crono->estado,
                    ];
                });

            return response()->json(['cronogramas' => $cronogramas]);

        } catch (\Exception $e) {
            // ✅ Registrar el error real para depuración
            \Log::error('Error en detallesCredito: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'usuario' => auth()->id(),
            ]);

            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
    
    // Vista principal de reportes de ventas
    public function ventas()
    {
        $empresa = Empresa::first();
        return view('reportes.ventas.index',compact('empresa'));
    }

    // Reporte de créditos por cliente
    public function creditosPorCliente(Request $request)
    {
        $nombre = $request->get('nombre');
        
        $ventas = Venta::with(['cliente', 'lote'])
            ->when($nombre, function ($query, $nombre) {
                $query->whereHas('cliente', function ($q) use ($nombre) {
                    $q->where('nombre_cliente', 'like', "%{$nombre}%");
                });
            })
            ->where('inicial', '>', 0) // Solo ventas con crédito
            ->paginate(15);

        return view('reportes.ventas.creditos_cliente', compact('ventas', 'nombre'));
    }

    // Métodos para PDFs (solo redirección a vistas por ahora)
    public function listaVentasPdf(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $query = Venta::with(['cliente', 'lote', 'metodopago']);

        if ($fechaDesde && $fechaHasta) {
            $query->whereBetween('created_at', [$fechaDesde, $fechaHasta]);
        } elseif ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        } elseif ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        $ventas = $query->latest()->get();
        $empresa = \App\Models\Empresa::first();

        return view('reportes.ventas.pdf.lista', compact('ventas', 'empresa', 'fechaDesde', 'fechaHasta'));
    }

    public function detalleVentasPdf()
    {
        return view('reportes.ventas.pdf.detalle');
    }

    public function consolidadoPdf(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        if (!$fechaDesde || !$fechaHasta) {
            return redirect()->back()->with('error', 'Debe seleccionar un rango de fechas completo.');
        }

        $empresa = \App\Models\Empresa::first();

        // ✅ Eliminar orderBy de concepto.nombre
        $movimientos = \App\Models\Movimiento::with('concepto')
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderBy('tipo', 'desc') // Solo ordenar por tipo en la BD
            ->get()
            ->sortBy([ // Ordenar por concepto en PHP
                ['tipo', 'desc'],
                ['concepto.nombre', 'asc']
            ]);

        $agrupado = $movimientos->groupBy('concepto.nombre')->map(function ($items) {
            return [
                'tipo' => $items->first()->tipo,
                'total' => $items->sum('monto'),
                'concepto_nombre' => $items->first()->concepto->nombre,
            ];
        })->values();

        $totalIngresos = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $movimientos->where('tipo', 'egreso')->sum('monto');
        $utilidad = $totalIngresos - $totalEgresos;

        return view('reportes.ventas.pdf.consolidado', compact(
            'empresa',
            'fechaDesde',
            'fechaHasta',
            'agrupado',
            'totalIngresos',
            'totalEgresos',
            'utilidad'
        ));
    }

    public function cuotasPendientesPdf()
    {
        return view('reportes.ventas.pdf.cuotas_pendientes');
    }

    public function cuotasMesPDF(Request $request)
    {
        $anio = $request->input('anio', now()->year);
        $mes = $request->input('mes', now()->month);

        // Asegurarse de que mes sea de 2 dígitos
        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

        // Consultar cronogramas del mes/año específico
        $cronogramas = \App\Models\Cronograma::whereYear('fecha_pago', $anio)
            ->whereMonth('fecha_pago', $mes)
            // ->where('estado', 'pendiente') // Si solo quiere pendientes, descomente esta línea
            ->with(['venta.cliente', 'venta.lote'])
            ->get();

        $empresa = \App\Models\Empresa::first();

        return view('reportes.ventas.pdf.cuotas_mes', compact('cronogramas', 'empresa', 'anio', 'mes'));
    }

    public function aniosDisponibles()
    {
        // Obtener años únicos de la columna 'fecha_pago' en la tabla 'ventas'
        $anios = \App\Models\Venta::selectRaw('YEAR(fecha_pago) as anio')
            ->groupBy('anio')
            ->orderBy('anio', 'desc')
            ->pluck('anio')
            ->toArray();

        return response()->json($anios);
    }
}