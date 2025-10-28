<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapImage;
use Illuminate\Support\Facades\Storage;

class MapImageController extends Controller
{
    public function index()
    {
        $mapImage = MapImage::first(); // o el que corresponda
        $position = $mapImage ? json_decode($mapImage->position, true) : null;
        return view('map.edit', compact('mapImage', 'position'));
        // $mapImage = MapImage::latest()->first();
        // return view('map.edit', compact('mapImage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        $path = $request->file('image')->store('map_images', 'public');

        // Buscar imagen existente
        $mapImage = MapImage::first();

        if ($mapImage) {
            // Eliminar imagen anterior
            Storage::disk('public')->delete($mapImage->image_path);
            // Actualizar con nueva imagen
            $mapImage->update([
                'image_path' => $path,
                'position' => null, // opcional: resetear posición
            ]);
        } else {
            // Crear nuevo registro si no existe
            $mapImage = MapImage::create([
                'name' => $request->name ?? 'Plano',
                'image_path' => $path,
            ]);
        }

        return redirect()->route('map.edit')->with('success', 'Imagen subida/reemplazada correctamente.');
    }


    public function updateMapPosition(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|array',
            'position.*' => 'array',
            'position.*.*' => 'numeric'
        ]);

        $mapImage = MapImage::findOrFail($id);
        $mapImage->position = json_encode($request->position);
        $mapImage->save();

        return response()->json(['success' => true]);
    }
}
