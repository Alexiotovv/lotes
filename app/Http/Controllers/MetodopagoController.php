<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;

class MetodopagoController extends Controller
{
    public function index()
    {
        $metodopagos = Metodopago::all();
        return view('metodopagos.index', compact('metodopagos'));
    }

    public function create()
    {
        return view('metodopagos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:metodopagos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        $validated['activo'] = $request->has('activo');
        Metodopago::create($validated);

        return redirect()->route('metodopagos.index')->with('success', 'Método de pago creado correctamente.');
    }

    public function edit(Metodopago $metodopago)
    {
        return view('metodopagos.edit', compact('metodopago'));
    }

    public function update(Request $request, Metodopago $metodopago)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:metodopagos,nombre,' . $metodopago->id,
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        $validated['activo'] = $request->has('activo');
        $metodopago->update($validated);

        return redirect()->route('metodopagos.index')->with('success', 'Método de pago actualizado correctamente.');
    }

    public function destroy(Metodopago $metodopago)
    {
        $metodopago->delete();
        return redirect()->route('metodopagos.index')->with('success', 'Método de pago eliminado correctamente.');
    }
}
