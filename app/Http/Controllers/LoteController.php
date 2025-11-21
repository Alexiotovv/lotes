<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\EstadoLote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
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
        try {
            $rules = [
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
            ];

            // ✅ Aplicar regla unique solo si el código realmente cambió
            if ($request->codigo !== $lote->codigo) {
                $rules['codigo'] = 'required|string|max:50|unique:lotes,codigo';
                \Log::info('🔀 Código cambió, aplicando regla unique');
            } else {
                $rules['codigo'] = 'required|string|max:50';
                \Log::info('✅ Mismo código, omitiendo regla unique');
            }

            $validated = $request->validate($rules);

            \Log::info('✅ Validación pasada, actualizando...');
            $lote->update($validated);
            \Log::info('✅ Lote actualizado correctamente');

            return response()->json([
                'success' => true, 
                'message' => '✅ Lote actualizado correctamente',
                'data' => $lote
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Error validación: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => '❌ Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('❌ Error general: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Error al actualizar el lote: ' . $e->getMessage(),
            ], 500);
        }
    }
    // public function update(Request $request, Lote $lote)
    // {
    //     $validated = $request->validate([
    //         // 'codigo' => 'required|string|max:50|unique:lotes,codigo,' . $lote->id,
    //         'codigo' => 'required|string|max:50|unique:lotes,codigo,' . $lote->id . ',id',
    //         'nombre' => 'nullable|string|max:100',
    //         'area_m2' => 'nullable|numeric',
    //         'frente' => 'nullable|numeric',
    //         'lado_izquierdo' => 'nullable|numeric',
    //         'lado_derecho' => 'nullable|numeric',
    //         'fondo' => 'nullable|numeric',
    //         'coordenadas' => 'nullable|string',
    //         'latitud' => 'nullable|numeric',
    //         'longitud' => 'nullable|numeric',
    //         'orientacion' => 'nullable|string|max:50',
    //         'precio_m2' => 'nullable|numeric',
    //         // 'precio_total' => 'nullable|numeric',
    //         'estado_lote_id' => 'required|exists:estado_lotes,id',
    //         'descripcion' => 'nullable|string',
    //     ]);

    //     $lote->update($validated);

    //     return response()->json(['success' => true, 'message' => '✅ Lote actualizado correctamente']);
    // }


    public function destroy(Lote $lote)
    {
        $lote->delete();
        return response()->json(['success' => true, 'message' => '🗑️ Lote eliminado correctamente']);
    }
}
