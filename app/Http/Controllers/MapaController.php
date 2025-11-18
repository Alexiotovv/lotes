<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Lote;
use App\Models\EstadoLote;
use App\Models\Empresa;
use App\Models\ConfiguracionGeneral;
use App\Models\Movimiento;
use App\Models\Concepto;
use App\Models\Caja;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MapaController extends Controller
{
    
    public function index()
    {   
        $lotes = Lote::with('estadoLote')->get();
        $estados = EstadoLote::all();
        
        // ✅ Cargar configuración del mapa desde la base de datos
        $mapConfig = \App\Models\MapImage::first();
        
        // ✅ Cargar imágenes superpuestas
        $imagenesSuperpuestas = [];
        if ($mapConfig) {
            $imagenesSuperpuestas = \App\Models\ImagenSuperpuesta::where('map_image_id', $mapConfig->id)
                ->where('activo', true)
                ->get()
                ->map(function($imagen) {
                    $data = $imagen->toArray();
                    $data['url_completa'] = asset('storage/' . $imagen->ruta_imagen);
                    return $data;
                });
        }
        return view('mapa.index', compact('lotes', 'estados', 'imagenesSuperpuestas', 'mapConfig'));
        
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

        // ✅ Cargar configuración del mapa desde la base de datos
        $mapConfig = \App\Models\MapImage::first();
        
        // ✅ Cargar imágenes superpuestas
        $imagenesSuperpuestas = [];
        if ($mapConfig) {
            $imagenesSuperpuestas = \App\Models\ImagenSuperpuesta::where('map_image_id', $mapConfig->id)
                ->where('activo', true)
                ->get()
                ->map(function($imagen) {
                    $data = $imagen->toArray();
                    $data['url_completa'] = asset('storage/' . $imagen->ruta_imagen);
                    return $data;
                });
        }

        return view('mapa.create', compact('lotes', 'prefijos', 'mapConfig', 'imagenesSuperpuestas'));
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

        // Obtener configuración
        $config = ConfiguracionGeneral::first();
        $registrarCompra = $config?->registrar_lote_compra ?? false;
        $montoCompra = $config?->monto_compra_lote ?? 0;

        // Validar si se puede registrar compras
        if ($registrarCompra && $montoCompra <= 0) {
            return response()->json([
                'success' => false,
                'message' => '❌ El monto de compra por lote debe ser mayor a 0.'
            ], 400);
        }

        // Obtener caja y concepto una sola vez (fuera del bucle)
        $cajaId = null;
        $conceptoId = null;

        if ($registrarCompra && $montoCompra > 0) {
            // Caja: primer registro activo
            $caja = Caja::where('activo', true)->first();
            if (!$caja) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ No hay cajas activas para registrar la compra.'
                ], 500);
            }
            $cajaId = $caja->id;

            // Concepto: egreso con 'compra' o 'terreno', o primer egreso
            $concepto = Concepto::where('tipo', 'egreso')
                ->where(function ($query) {
                    $query->where('nombre', 'like', '%compra%')
                        ->orWhere('nombre', 'like', '%terreno%');
                })
                ->first() ?? Concepto::where('tipo', 'egreso')->first();

            if (!$concepto) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ No se encontró un concepto de egreso para compras.'
                ], 500);
            }
            $conceptoId = $concepto->id;
        }

        $creados = [];
        DB::transaction(function () use ($lotes, $registrarCompra, $montoCompra, $cajaId, $conceptoId, &$creados) {
            foreach ($lotes as $lote) {
                // Crear lote
                $nuevo = \App\Models\Lote::create([
                    'codigo' => $lote['codigo'],
                    'latitud' => $lote['latitud'],
                    'longitud' => $lote['longitud'],
                    'estado_lote_id' => 1, // Disponible
                ]);
                $creados[] = $nuevo;

                // Registrar movimiento si corresponde
                if ($registrarCompra && $montoCompra > 0 && $cajaId && $conceptoId) {
                    Movimiento::create([
                        'caja_id' => $cajaId,
                        'concepto_id' => $conceptoId,
                        'venta_id' => null,
                        'user_id' => auth()->id(),
                        'referencia' => 'Compra Lote ' . $nuevo->codigo,
                        'monto' => $montoCompra,
                        'tipo' => 'egreso',
                        'fecha' => now()->format('Y-m-d'),
                        'descripcion' => 'Registro automático de compra de lote',
                    ]);
                }
            }
        });

        $mensaje = '✅ Lotes guardados correctamente.';
        if ($registrarCompra && $montoCompra > 0) {
            $mensaje .= ' Movimientos de compra registrados.';
        }

        return response()->json([
            'success' => true,
            'message' => $mensaje,
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
        
        // ✅ Cargar configuración del mapa desde la base de datos
        $mapConfig = \App\Models\MapImage::first();
        
        // ✅ Cargar imágenes superpuestas
        $imagenesSuperpuestas = [];
        if ($mapConfig) {
            $imagenesSuperpuestas = \App\Models\ImagenSuperpuesta::where('map_image_id', $mapConfig->id)
                ->where('activo', true)
                ->get()
                ->map(function($imagen) {
                    $data = $imagen->toArray();
                    $data['url_completa'] = asset('storage/' . $imagen->ruta_imagen);
                    return $data;
                });
        }

        return view('mapa.ver-lotes', compact('lotes', 'empresa', 'mapConfig', 'imagenesSuperpuestas'));
    }

}
