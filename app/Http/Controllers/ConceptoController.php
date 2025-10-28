<?php

namespace App\Http\Controllers;

use App\Models\Concepto;
use Illuminate\Http\Request;

class ConceptoController extends Controller
{
    public function index()
    {
        $conceptos = Concepto::latest()->get();
        return view('tesoreria.conceptos.index', compact('conceptos'));
    }

    public function create()
    {
        return view('tesoreria.conceptos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ingreso,egreso',
            'categoria' => 'nullable|string|max:100',
        ]);

        Concepto::create($request->all());
        return redirect()->route('tesoreria.conceptos.index')->with('success', 'Concepto creado correctamente.');
    }

    public function edit(Concepto $concepto)
    {
        return view('tesoreria.conceptos.edit', compact('concepto'));
    }

    public function update(Request $request, Concepto $concepto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:ingreso,egreso',
            'categoria' => 'nullable|string|max:100',
        ]);

        $concepto->update($request->all());
        return redirect()->route('tesoreria.conceptos.index')->with('success', 'Concepto actualizado correctamente.');
    }

    public function toggle(Concepto $concepto)
    {
        $concepto->activo = !$concepto->activo;
        $concepto->save();
        return back()->with('success', 'Estado del concepto actualizado.');
    }
}