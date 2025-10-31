<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Lote;
use App\Models\EstadoLote;
use App\Models\Empresa;
class MapaController extends Controller
{
    // app/Http/Controllers/MapaController.php
    public function index()
    {
        $lotes = Lote::with('estadoLote')->get();
        $estados = EstadoLote::all();
        return view('mapa.index',compact('lotes','estados'));
    }

    public function createLote()
    {
        $lotes = Lote::select('codigo', 'latitud', 'longitud')
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        // Obtener prefijos únicos (primera letra de código)
        $prefijos = Lote::selectRaw('DISTINCT SUBSTRING(codigo, 1, 1) as prefijo')
            ->where('codigo', 'like', '_%')
            ->pluck('prefijo')
            ->sort()
            ->values();

        return view('mapa.create', compact('lotes', 'prefijos'));
    }

    public function guardarLotes(Request $request)
    {
        $lotes = $request->input('lotes');

        if (!$lotes || !is_array($lotes)) {
            return response()->json([
                'success' => false,
                'message' => '❌ No se recibieron lotes válidos'
            ], 400);
        }

        $creados = [];
        foreach ($lotes as $lote) {
            $nuevo = Lote::create([
                'codigo' => $lote['codigo'],
                'latitud' => $lote['latitud'],
                'longitud' => $lote['longitud'],
                'estado_lote_id' => 1, // Disponible por defecto
            ]);
            $creados[] = $nuevo;
        }

        return response()->json([
            'success' => true,
            'message' => '✅ Lotes guardados correctamente',
            'data' => $creados
        ]);
    }
    
   public function updatePosition(Request $request, $id)
    {
        $mapImage = MapImage::findOrFail($id);
        $mapImage->position = json_encode($request->input('corners'));
        $mapImage->save();

        return response()->json(['success' => true]);
    }

    public function verLotes()
    {
        $lotes = Lote::with('estadoLote')->get();
        $empresa = Empresa::first();
        return view('mapa.ver-lotes', compact('lotes','empresa'));
    }

}
