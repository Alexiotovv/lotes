<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cronograma;
use App\Models\Pago;
use App\Models\Caja;
use Illuminate\Http\Request;
class PagoController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'lote', 'cronogramas' => function($q) {
            $q->where('estado', 'pendiente')->orWhere('estado', 'vencido');
        }]);

        // Búsqueda por cliente, lote, ID de venta o fecha de venta (created_at)
        if ($search = $request->input('search')) {
            // ✅ Agregue esta línea para ver qué valor tiene $search
            \Log::info('Valor de search:', ['search' => $search]);

            $query->where(function ($q) use ($search) {
                // Buscar por cliente: nombre o dni_ruc
                $q->whereHas('cliente', function ($q2) use ($search) {
                    $q2->where('nombre_cliente', 'LIKE', "%$search%")
                    ->orWhere('dni_ruc', 'LIKE', "%$search%");
                })
                // Buscar por lote: código o nombre
                ->orWhereHas('lote', function ($q2) use ($search) {
                    $q2->where('codigo', 'LIKE', "%$search%")
                    ->orWhere('nombre', 'LIKE', "%$search%");
                })
                // ✅ Buscar por fecha de venta exacta (created_at) en formato YYYY-MM-DD
                ->orWhereRaw("DATE(created_at) like ?", ["%$search%"])
                // Buscar por ID de venta
                ->orWhere('id', 'LIKE', "%$search%");
            });
        }

        // Paginar ventas
        $ventas = $query->latest()->paginate(15)->appends($request->query());

        // Cajas activas
        $cajas = Caja::where('activo', true)->get();

        return view('pagos.index', compact('ventas', 'cajas'));
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
 


    public function store(Request $request)
    {
        $validated = $request->validate([
            'cronograma_id' => 'required|exists:cronogramas,id',
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string|max:50',
            'referencia' => 'nullable|string|max:100|unique:pagos,referencia', // ✅ Validación de unicidad
            'voucher' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048',
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

        // Registrar en tesorería
        \App\Http\Controllers\TesoreriaController::registrarIngresoVenta(
            ventaId: $cronograma->venta_id,
            cajaId: $request->caja_id,
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

        $venta = $cronograma->venta;
        if ($venta && method_exists($venta, 'isFinalizada') && $venta->isFinalizada()) {
            $venta->estado = 'finalizado';
            $venta->save();
        }

        return back()->with('success', 'Pago registrado correctamente.');
    }
}