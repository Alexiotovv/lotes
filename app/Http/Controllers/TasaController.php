<?php

namespace App\Http\Controllers;

use App\Models\Tasa;
use Illuminate\Http\Request;

class TasaController extends Controller
{
    public function index()
    {
        $tasas = Tasa::latest()->paginate(15);
        return view('tasas.index', compact('tasas'));
    }

    public function create()
    {
        return view('tasas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0|max:1',
        ]);

        Tasa::create($request->only(['nombre', 'monto']));

        return redirect()->route('tasas.index')->with('success', 'Tasa creada correctamente.');
    }

    public function edit(Tasa $tasa)
    {
        return view('tasas.edit', compact('tasa'));
    }

    public function update(Request $request, Tasa $tasa)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0|max:1',
        ]);

        $tasa->update($request->only(['nombre', 'monto']));

        return redirect()->route('tasas.index')->with('success', 'Tasa actualizada correctamente.');
    }

    public function destroy(Tasa $tasa)
    {
        $tasa->delete();
        return redirect()->route('tasas.index')->with('success', 'Tasa eliminada correctamente.');
    }

    
}