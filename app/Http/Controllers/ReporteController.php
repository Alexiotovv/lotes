<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
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

    public function cuotasMesPdf()
    {
        return view('reportes.ventas.pdf.cuotas_mes');
    }
}