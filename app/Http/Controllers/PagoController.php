<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cronograma;
use App\Models\Pago;
use App\Models\Caja;
use Illuminate\Http\Request;
class PagoController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['cliente', 'cronogramas' => function($q) {
            $q->where('estado', 'pendiente')->orWhere('estado', 'vencido');
        }])->get();

        // Cargar cajas activas para el formulario de cobro
        $cajas = Caja::where('activo', true)->get();

        return view('pagos.index', compact('ventas','cajas'));
    }

    public function detalle(Venta $venta)
    {
        $pagos = Pago::with('cronograma') // ← ¡Cargar la relación!
            ->whereIn('cronograma_id', $venta->cronogramas->pluck('id'))
            ->get()
            ->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'fecha_pago' => $pago->fecha_pago,
                    'monto_pagado' => $pago->monto_pagado,
                    'metodo_pago' => $pago->metodo_pago,
                    'referencia' => $pago->referencia,
                    'voucher' => $pago->voucher,
                    'nro_cuota' => $pago->cronograma ? $pago->cronograma->nro_cuota : null, // ← Nueva columna
                ];
            });

        return response()->json($pagos);
    }
    public function cobrar(Venta $venta)
    {
        $cuotas = $venta->cronogramas()
            ->orderBy('nro_cuota')
            ->get()
            ->map(function ($c) {
                $pagado = $c->pagos->sum('monto_pagado');
                $pendiente = max(0, $c->cuota - $pagado);
                return [
                    'id' => $c->id,
                    'nro_cuota' => $c->nro_cuota,
                    'fecha_pago' => $c->fecha_pago,
                    'cuota_total' => $c->cuota,
                    'pagado' => $pagado,
                    'pendiente' => $pendiente,
                    'estado' => $c->estado, // 'pendiente', 'vencido', 'pagado'
                ];
            });

        return response()->json($cuotas);
    }
    // public function cobrar(Venta $venta)
    // {
    //     $cuotas = $venta->cronogramas()
    //         ->where('estado', '!=', 'pagado')
    //         ->orderBy('nro_cuota')
    //         ->get()
    //         ->map(function ($c) {
    //             $pagado = $c->pagos->sum('monto_pagado');
    //             $pendiente = max(0, $c->cuota - $pagado);
    //             return [
    //                 'id' => $c->id,
    //                 'nro_cuota' => $c->nro_cuota,
    //                 'fecha_pago' => $c->fecha_pago,
    //                 'cuota_total' => $c->cuota,
    //                 'pagado' => $pagado,
    //                 'pendiente' => $pendiente,
    //                 'estado' => $c->estado,
    //             ];
    //         });

    //     return response()->json($cuotas);
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'cronograma_id' => 'required|exists:cronogramas,id',
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string|max:50',
            'referencia' => 'nullable|string|max:100',
            'voucher' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // hasta 2MB
            'observacion' => 'nullable|string',
        ]);

        // Verificar saldo pendiente
        $cronograma = Cronograma::findOrFail($validated['cronograma_id']);
        $pagadoActual = $cronograma->pagos->sum('monto_pagado');
        $saldoPendiente = max(0, $cronograma->cuota - $pagadoActual);

        if ($validated['monto_pagado'] > $saldoPendiente) {
            return back()->withErrors(['monto_pagado' => 'El monto no puede exceder el saldo pendiente de S/ ' . number_format($saldoPendiente, 2)]);
        }

        // Subir voucher si existe
        $voucherPath = null;
        if ($request->hasFile('voucher')) {
            $voucherPath = $request->file('voucher')->store('vouchers', 'public');
        }

        Pago::create(array_merge($validated, ['voucher' => $voucherPath]));

        // En PagoController@store, después de Pago::create(...)
        \App\Http\Controllers\TesoreriaController::registrarIngresoVenta(
            ventaId: $cronograma->venta_id,
            cajaId: $request->caja_id, // ¡Asegúrese de enviar caja_id desde el formulario de cobro!
            monto: $validated['monto_pagado'],
            fecha: $validated['fecha_pago'],
            referencia: $validated['referencia'] ?? null
        );


        // Actualizar estado del cronograma
        $nuevoPagado = $pagadoActual + $validated['monto_pagado'];
        if ($nuevoPagado >= $cronograma->cuota) {
            $cronograma->estado = 'pagado';
            $cronograma->save();
        }

        return back()->with('success', 'Pago registrado correctamente.');
    }
}