<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $cajas = Caja::latest()->get();
        return view('tesoreria.cajas.index', compact('cajas'));
    }

    public function create()
    {
        return view('tesoreria.cajas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:efectivo,banco,digital',
            'saldo_inicial' => 'nullable|numeric|min:0',
        ]);

        Caja::create($request->all());
        return redirect()->route('tesoreria.cajas.index')->with('success', 'Caja creada correctamente.');
    }

    public function edit(Caja $caja)
    {
        return view('tesoreria.cajas.edit', compact('caja'));
    }

    public function update(Request $request, Caja $caja)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:efectivo,banco,digital',
            'saldo_inicial' => 'nullable|numeric|min:0',
        ]);

        $caja->update($request->all());
        return redirect()->route('tesoreria.cajas.index')->with('success', 'Caja actualizada correctamente.');
    }

    public function toggle(Caja $caja)
    {
        $caja->activo = !$caja->activo;
        $caja->save();
        return back()->with('success', 'Estado de la caja actualizado.');
    }
}