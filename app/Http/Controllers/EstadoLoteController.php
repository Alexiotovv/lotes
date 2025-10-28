<?php

namespace App\Http\Controllers;

use App\Models\EstadoLote;
use Illuminate\Http\Request;

class EstadoLoteController extends Controller
{
    public function index()
    {
        $estados = EstadoLote::latest()->get();
        return view('estado_lotes.index', compact('estados'));
    }

    public function create()
    {
        return view('estado_lotes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estado' => 'required|string|max:50|unique:estado_lotes,estado',
            'color' => 'required|string|max:20',
        ]);

        EstadoLote::create($validated);

        return redirect()->route('estado_lotes.index')->with('success', 'Estado guardado correctamente.');
    }

    public function edit(EstadoLote $estado_lote)
    {
        return view('estado_lotes.edit', compact('estado_lote'));
    }

    public function update(Request $request, EstadoLote $estado_lote)
    {
        $validated = $request->validate([
            'estado' => 'required|string|max:50|unique:estado_lotes,estado,' . $estado_lote->id,
            'color' => 'required|string|max:20',
        ]);

        $estado_lote->update($validated);

        return redirect()->route('estado_lotes.index')->with('success', 'Estado actualizado correctamente.');
    }

    public function destroy(EstadoLote $estado_lote)
    {
        $estado_lote->delete();
        return redirect()->route('estado_lotes.index')->with('success', 'Estado eliminado correctamente.');
    }
}
