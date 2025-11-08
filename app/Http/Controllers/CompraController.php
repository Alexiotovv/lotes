<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Concepto;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompraController extends Controller
{
    public function index()
    {
        // Obtener IDs de conceptos cuya categoría contenga "compra"
        $conceptoIds = Concepto::where('categoria', 'like', '%compra%')
            ->pluck('id');

        $compras = Movimiento::with(['caja', 'concepto', 'user'])
            ->whereIn('concepto_id', $conceptoIds)
            ->where('tipo', 'egreso')
            ->latest()
            ->paginate(15);

        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        $cajas = Caja::where('activo', true)->get();
        $conceptos = Concepto::where('tipo', 'egreso')
            ->where('nombre', 'like', '%compra%')
            ->orWhere('nombre', 'Compra de Terreno')
            ->get();

        // Si no hay conceptos de compra, usar todos los egresos
        if ($conceptos->isEmpty()) {
            $conceptos = Concepto::where('tipo', 'egreso')->get();
        }

        return view('compras.create', compact('cajas', 'conceptos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'concepto_id' => 'required|exists:conceptos,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'referencia' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'comprobante' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Verificar que el concepto sea de tipo 'egreso'
        $concepto = Concepto::findOrFail($request->concepto_id);
        if ($concepto->tipo !== 'egreso') {
            return back()->withErrors(['concepto_id' => 'El concepto debe ser de tipo egreso.']);
        }

        $comprobantePath = null;
        if ($request->hasFile('comprobante')) {
            $comprobantePath = $request->file('comprobante')->store('comprobantes/compras', 'public');
        }

        Movimiento::create([
            'caja_id' => $request->caja_id,
            'concepto_id' => $request->concepto_id,
            'venta_id' => null,
            'user_id' => auth()->id(),
            'referencia' => $request->referencia,
            'monto' => $request->monto,
            'tipo' => 'egreso',
            'fecha' => $request->fecha,
            'descripcion' => $request->descripcion,
            'comprobante' => $comprobantePath,
        ]);

        return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');
    }
}