<?php

namespace App\Http\Controllers;

use App\Models\MapImage;
use App\Models\ImagenPosicionada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MapImageController extends Controller
{
    public function index()
    {
        $mapImage = MapImage::first();
        $position = $mapImage ? json_decode($mapImage->position, true) : null;

        // ✅ Ya no necesita buscar en otra tabla
        $posicionGuardada = $mapImage ? [
            'pos_x' => $mapImage->pos_x,
            'pos_y' => $mapImage->pos_y,
            'escala' => $mapImage->escala,
        ] : null;

        return view('map.edit', compact('mapImage', 'position', 'posicionGuardada'));
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

    // ✅ Nuevo: Guardar posición personalizada de imagen (X, Y, Escala)
    public function actualizarPosicion(Request $request)
    {
        $request->validate([
            'lat_map' => 'required|numeric|between:-90,90',
            'lon_map' => 'required|numeric|between:-180,180',
            'zoom_actual' => 'required|integer|min:1|max:22',
        ]);

        // ✅ Buscar o crear el registro
        $mapImage = MapImage::firstOrCreate([], [
            'name' => 'Mapa Principal',
            'ruta_imagen' => null, // o una imagen por defecto
            'pos_x' => 0,
            'pos_y' => 0,
            'escala' => 1.0000,
            'lat_map' => -3.844051, // valor por defecto
            'lon_map' => -73.3432986,
            'max_zoom_map' => 19,
            'min_zoom_map' => 15,
        ]);

        // ✅ Actualizar con los nuevos valores
        $mapImage->update([
            'lat_map' => $request->lat_map,
            'lon_map' => $request->lon_map,
            'actual_zoom_map' => $request->zoom_actual,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Posición del mapa actualizada correctamente.',
            'data' => $mapImage
        ]);
    }

    // ✅ Nuevo: Obtener posición guardada
    public function obtenerPosicionImagen(Request $request)
    {
        $nombre = $request->query('nombre', 'mapa_principal'); // Por defecto
        $registro = ImagenPosicionada::where('nombre', $nombre)->first();

        if (!$registro) {
            return response()->json(['success' => false, 'message' => 'No hay datos guardados.']);
        }

        return response()->json([
            'success' => true,
            'data' => $registro,
        ]);
    }
}