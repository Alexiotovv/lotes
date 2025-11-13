<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditoController extends Controller
{
    public function index()
    {
        $query = Venta::with(['cliente', 'lote'])
            ->select('ventas.*')
            ->addSelect([
                'cliente_nombre' => DB::table('clientes')
                    ->whereColumn('clientes.id', 'ventas.cliente_id')
                    ->select('nombre_cliente')
                    ->limit(1),
                'lote_precio_total' => DB::table('lotes')
                    ->whereColumn('lotes.id', 'ventas.lote_id')
                    ->select(DB::raw('COALESCE(area_m2 * precio_m2, 0)'))
                    ->limit(1),
                'total_pagado' => DB::table('cronogramas')
                    ->join('pagos', 'cronogramas.id', '=', 'pagos.cronograma_id')
                    ->whereColumn('cronogramas.venta_id', 'ventas.id')
                    ->select(DB::raw('COALESCE(SUM(pagos.monto_pagado), 0)')),
                'proxima_cuota_fecha' => DB::table('cronogramas')
                    ->whereColumn('cronogramas.venta_id', 'ventas.id')
                    ->where('cronogramas.estado', 'pendiente')
                    ->orderBy('nro_cuota')
                    ->select('fecha_pago')
                    ->limit(1),
                'estado_proxima' => DB::table('cronogramas')
                    ->whereColumn('cronogramas.venta_id', 'ventas.id')
                    ->where('cronogramas.estado', 'pendiente')
                    ->orderBy('nro_cuota')
                    ->select(DB::raw("CASE 
                        WHEN fecha_pago < CURDATE() THEN 'vencido'
                        ELSE 'vigente'
                    END"))
                    ->limit(1),
            ]);

        // Aplicar búsqueda si existe
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ventas.id', 'like', "%{$search}%")
                ->orWhere('ventas.created_at', 'like', "%{$search}%")
                ->orWhereHas('cliente', fn($q2) => $q2->where('nombre_cliente', 'like', "%{$search}%"))
                ->orWhereHas('lote', fn($q2) => $q2->where('codigo', 'like', "%{$search}%")->orWhere('nombre', 'like', "%{$search}%"));
            });
        }

        $creditos = $query->orderBy('ventas.created_at', 'desc')->paginate(10);

        // Calcular total_deuda en PHP (más seguro)
        foreach ($creditos as $credito) {
            $credito->total_deuda = max(0, $credito->monto_financiar - ($credito->total_pagado ?? 0));
        }

        return view('creditos.index', compact('creditos'));
    }

    public function calendario(Venta $venta)
    {
        $cronogramas = $venta->cronogramas()->orderBy('nro_cuota')->get();
        $empresa = \App\Models\Empresa::first();

        return view('creditos.calendario', compact('venta', 'cronogramas', 'empresa'));
    }

    public function pagos(Venta $venta)
    {
        $pagos = $venta->cronogramas()
            ->with('pagos')
            ->orderBy('nro_cuota')
            ->get();

        // Calcular saldos acumulados
        $saldoInicial = $venta->monto_financiar;
        $totalAmortizado = 0;

        foreach ($pagos as $cronograma) {
            $pagoTotal = $cronograma->pagos->sum('monto_pagado');
            $saldoActual = max(0, $saldoInicial - $totalAmortizado);
            $cronograma->saldo_inicial = $saldoActual;
            $cronograma->saldo_final = max(0, $saldoActual - $pagoTotal);
            $totalAmortizado += $pagoTotal;
        }

        $empresa = \App\Models\Empresa::first();

        return view('creditos.pagos', compact('venta', 'pagos', 'empresa'));
    }
}