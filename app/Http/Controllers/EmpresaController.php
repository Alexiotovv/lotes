<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresa = Empresa::first(); // Solo hay una empresa
        return view('empresas.edit', compact('empresa'));
    }


    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|size:11|unique:empresas,ruc,' . $empresa->id,
            'direccion' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'departamento' => 'nullable|string',
            'provincia' => 'nullable|string',
            'distrito' => 'nullable|string',
            'telefono' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // hasta 2MB
        ]);

        // Manejar subida de logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                Storage::disk('public')->delete($empresa->logo);
            }

            // Guardar nuevo logo
            $path = $request->file('logo')->store('logos', 'public');
            $empresa->logo = $path;
        }

        $empresa->update($request->except('logo')); // Actualizar otros campos

        return Redirect::route('empresa.edit')->with('success', 'Información de la empresa actualizada correctamente.');
    }
}