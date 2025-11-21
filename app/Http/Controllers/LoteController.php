<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\EstadoLote;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function index()
    {
        $lotes = Lote::with('estadoLote')->get();
        return response()->json($lotes);
    }

    public function indexView()
    {
        $lotes = Lote::with('estadoLote')->get();
        return view('lotes.index', compact('lotes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:50|unique:lotes,codigo',
                'nombre' => 'nullable|string|max:100',
                'area_m2' => 'nullable|numeric',
                'frente' => 'nullable|numeric',
                'lado_izquierdo' => 'nullable|numeric',
                'lado_derecho' => 'nullable|numeric',
                'fondo' => 'nullable|numeric',
                'coordenadas' => 'nullable|string',
                'latitud' => 'nullable|numeric',
                'longitud' => 'nullable|numeric',
                'orientacion' => 'nullable|string|max:50',
                'precio_m2' => 'nullable|numeric',
                
                'estado_lote_id' => 'required|exists:estado_lotes,id',
                'descripcion' => 'nullable|string',
            ]);

            $lote = Lote::create($validated);

            return response()->json([
                'success' => true,
                'message' => '✅ Lote guardado correctamente',
                'data' => $lote
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error al guardar el lote: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Lote $lote)
    {
            \Log::info('🔍 INICIANDO ACTUALIZACIÓN');
            \Log::info('Lote ID: ' . $lote->id);
            \Log::info('Código recibido: ' . $request->codigo);
            \Log::info('Código actual en BD: ' . $lote->codigo);
            \Log::info('Request method: ' . $request->method());
        $validated = $request->validate([
            // 'codigo' => 'required|string|max:50|unique:lotes,codigo,' . $lote->id,
            'codigo' => 'required|string|max:50|unique:lotes,codigo,' . $lote->id . ',id',
            'nombre' => 'nullable|string|max:100',
            'area_m2' => 'nullable|numeric',
            'frente' => 'nullable|numeric',
            'lado_izquierdo' => 'nullable|numeric',
            'lado_derecho' => 'nullable|numeric',
            'fondo' => 'nullable|numeric',
            'coordenadas' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'orientacion' => 'nullable|string|max:50',
            'precio_m2' => 'nullable|numeric',
            // 'precio_total' => 'nullable|numeric',
            'estado_lote_id' => 'required|exists:estado_lotes,id',
            'descripcion' => 'nullable|string',
        ]);

        $lote->update($validated);

        return response()->json(['success' => true, 'message' => '✅ Lote actualizado correctamente']);
    }


    public function destroy(Lote $lote)
    {
        $lote->delete();
        return response()->json(['success' => true, 'message' => '🗑️ Lote eliminado correctamente']);
    }
}
