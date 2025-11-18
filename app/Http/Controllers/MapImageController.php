<?php


namespace App\Http\Controllers;

use App\Models\MapImage;
use App\Models\ImagenSuperpuesta;
use App\Models\ImagenPosicionada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MapImageController extends Controller
{
    public function index()
    {
        $mapImage = MapImage::first();
        $position = $mapImage ? json_decode($mapImage->position, true) : null;

        // ✅ Cargar imágenes superpuestas
        $imagenesSuperpuestas = [];
        if ($mapImage) {
            $imagenesSuperpuestas = ImagenSuperpuesta::where('map_image_id', $mapImage->id)
                ->where('activo', true)
                ->get()
                ->map(function($imagen) {
                    // Convertir a array y agregar URL completa
                    $data = $imagen->toArray();
                    $data['url_completa'] = asset('storage/' . $imagen->ruta_imagen);
                    return $data;
                });
        }

        $posicionGuardada = $mapImage ? [
            'pos_x' => $mapImage->pos_x,
            'pos_y' => $mapImage->pos_y,
            'escala' => $mapImage->escala,
        ] : null;

        return view('map.edit', compact('mapImage', 'position', 'posicionGuardada', 'imagenesSuperpuestas'));
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


    // ✅ Nuevo: Guardar imagen superpuesta
    public function guardarImagenSuperpuesta(Request $request)
    {
        $request->validate([
            'image_data' => 'required|string', // Base64
            'lat_centro' => 'required|numeric|between:-90,90',
            'lng_centro' => 'required|numeric|between:-180,180',
            'ancho_lat' => 'required|numeric',
            'ancho_lng' => 'required|numeric',
            'escala' => 'required|numeric',
            'opacidad' => 'required|numeric|between:0,1',
        ]);

        // Obtener el mapa principal
        $mapImage = MapImage::first();
        
        if (!$mapImage) {
            return response()->json(['success' => false, 'message' => 'No hay configuración de mapa'], 404);
        }

        // Guardar la imagen en storage
        $rutaImagen = $this->guardarImagenBase64($request->image_data);

        // Crear registro en imagenes_superpuestas
        $imagenSuperpuesta = ImagenSuperpuesta::create([
            'map_image_id' => $mapImage->id,
            'ruta_imagen' => $rutaImagen,
            'lat_centro' => $request->lat_centro,
            'lng_centro' => $request->lng_centro,
            'ancho_lat' => $request->ancho_lat,
            'ancho_lng' => $request->ancho_lng,
            'escala' => $request->escala,
            'opacidad' => $request->opacidad,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Imagen superpuesta guardada correctamente.',
            'data' => $imagenSuperpuesta
        ]);
    }

    // ✅ En MapImageController - método para actualizar imagen existente
    public function actualizarImagenSuperpuesta(Request $request, $id)
    {
        $request->validate([
            'lat_centro' => 'required|numeric|between:-90,90',
            'lng_centro' => 'required|numeric|between:-180,180',
            'ancho_lat' => 'required|numeric',
            'ancho_lng' => 'required|numeric',
            'escala' => 'required|numeric',
            'opacidad' => 'required|numeric|between:0,1',
        ]);

        $imagen = ImagenSuperpuesta::find($id);
        
        if (!$imagen) {
            return response()->json(['success' => false, 'message' => 'Imagen no encontrada'], 404);
        }

        $imagen->update([
            'lat_centro' => $request->lat_centro,
            'lng_centro' => $request->lng_centro,
            'ancho_lat' => $request->ancho_lat,
            'ancho_lng' => $request->ancho_lng,
            'escala' => $request->escala,
            'opacidad' => $request->opacidad,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Imagen actualizada correctamente.',
            'data' => $imagen
        ]);
    }

    private function guardarImagenBase64($base64)
    {
        // Extraer la parte base64
        $imageData = explode(',', $base64);
        $image = base64_decode($imageData[1]);
        
        // Determinar extensión
        $extension = 'png';
        if (str_contains($imageData[0], 'jpeg') || str_contains($imageData[0], 'jpg')) {
            $extension = 'jpg';
        }
        
        // Generar nombre único
        $fileName = 'superpuesta_' . time() . '_' . uniqid() . '.' . $extension;
        $path = 'imagenes_superpuestas/' . $fileName;
        
        // Guardar en storage
        Storage::disk('public')->put($path, $image);
        
        return $path;
    }

    // ✅ Nuevo: Eliminar imagen superpuesta
    public function eliminarImagenSuperpuesta($id)
    {
        $imagen = ImagenSuperpuesta::find($id);
        
        if (!$imagen) {
            return response()->json(['success' => false, 'message' => 'Imagen no encontrada'], 404);
        }

        // Eliminar archivo físico
        Storage::disk('public')->delete($imagen->ruta_imagen);
        
        // Eliminar registro
        $imagen->delete();

        return response()->json([
            'success' => true,
            'message' => '✅ Imagen eliminada correctamente.'
        ]);
    }
}



// namespace App\Http\Controllers;

// use App\Models\MapImage;
// use App\Models\ImagenPosicionada;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// class MapImageController extends Controller
// {
//     public function index()
//     {
//         $mapImage = MapImage::first();
//         $position = $mapImage ? json_decode($mapImage->position, true) : null;

//         // ✅ Ya no necesita buscar en otra tabla
//         $posicionGuardada = $mapImage ? [
//             'pos_x' => $mapImage->pos_x,
//             'pos_y' => $mapImage->pos_y,
//             'escala' => $mapImage->escala,
//         ] : null;

//         return view('map.edit', compact('mapImage', 'position', 'posicionGuardada'));
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'image' => 'required|image',
//         ]);

//         $path = $request->file('image')->store('map_images', 'public');

//         // Buscar imagen existente
//         $mapImage = MapImage::first();

//         if ($mapImage) {
//             // Eliminar imagen anterior
//             Storage::disk('public')->delete($mapImage->image_path);
//             // Actualizar con nueva imagen
//             $mapImage->update([
//                 'image_path' => $path,
//                 'position' => null, // opcional: resetear posición
//             ]);
//         } else {
//             // Crear nuevo registro si no existe
//             $mapImage = MapImage::create([
//                 'name' => $request->name ?? 'Plano',
//                 'image_path' => $path,
//             ]);
//         }

//         return redirect()->route('map.edit')->with('success', 'Imagen subida/reemplazada correctamente.');
//     }

//     public function updateMapPosition(Request $request, $id)
//     {
//         $request->validate([
//             'position' => 'required|array',
//             'position.*' => 'array',
//             'position.*.*' => 'numeric'
//         ]);

//         $mapImage = MapImage::findOrFail($id);
//         $mapImage->position = json_encode($request->position);
//         $mapImage->save();

//         return response()->json(['success' => true]);
//     }

//     // ✅ Nuevo: Guardar posición personalizada de imagen (X, Y, Escala)
//     public function actualizarPosicion(Request $request)
//     {
//         $request->validate([
//             'lat_map' => 'required|numeric|between:-90,90',
//             'lon_map' => 'required|numeric|between:-180,180',
//             'zoom_actual' => 'required|integer|min:1|max:22',
//         ]);

//         // ✅ Buscar o crear el registro
//         $mapImage = MapImage::firstOrCreate([], [
//             'name' => 'Mapa Principal',
//             'ruta_imagen' => null, // o una imagen por defecto
//             'pos_x' => 0,
//             'pos_y' => 0,
//             'escala' => 1.0000,
//             'lat_map' => -3.844051, // valor por defecto
//             'lon_map' => -73.3432986,
//             'max_zoom_map' => 19,
//             'min_zoom_map' => 15,
//         ]);

//         // ✅ Actualizar con los nuevos valores
//         $mapImage->update([
//             'lat_map' => $request->lat_map,
//             'lon_map' => $request->lon_map,
//             'actual_zoom_map' => $request->zoom_actual,
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => '✅ Posición del mapa actualizada correctamente.',
//             'data' => $mapImage
//         ]);
//     }

//     // ✅ Nuevo: Obtener posición guardada
//     public function obtenerPosicionImagen(Request $request)
//     {
//         $nombre = $request->query('nombre', 'mapa_principal'); // Por defecto
//         $registro = ImagenPosicionada::where('nombre', $nombre)->first();

//         if (!$registro) {
//             return response()->json(['success' => false, 'message' => 'No hay datos guardados.']);
//         }

//         return response()->json([
//             'success' => true,
//             'data' => $registro,
//         ]);
//     }
// }