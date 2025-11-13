<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Venta;
use App\Models\Empresa;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index(Request $request)
    {
        $ventaId = $request->input('venta_id');
        $contratos = Contrato::where('venta_id', $ventaId)->orderBy('created_at', 'desc')->get();

        return response()->json($contratos->map(function ($c) {
            return [
                'id' => $c->id,
                'created_at' => $c->created_at,
                'activo' => $c->activo,
                'url' => route('contratos.ver', $c), // Asegúrese de tener esta ruta
            ];
        }));
    }

    // Para anular contrato (método destroy)
    public function destroy(Contrato $contrato)
    {
        $ventaId = $contrato->venta_id;
        $contrato->activo = false; // O puede usar soft deletes
        $contrato->save();

        return response()->json(['message' => 'Contrato anulado', 'venta_id' => $ventaId]);
    }

    public function generar(Venta $venta)
    {
        // Verificar que no exista un contrato activo
        if ($venta->contratos()->where('activo', true)->exists()) {
            return back()->with('warning', 'Ya existe un contrato activo para esta venta.');
        }

        // Generar contenido HTML del contrato
        $contenidoHtml = $this->generarContenidoHtml($venta);

        // Crear el contrato
        Contrato::create([
            'venta_id' => $venta->id,
            'user_id' => auth()->id(),
            'contenido_html' => $contenidoHtml,
            'activo' => true,
        ]);

        return redirect()->route('ventas.index')->with('success', 'Contrato generado correctamente.');
    }

    public function ver(Contrato $contrato)
    {
        // Mostrar el contrato en una página renderizada
        $venta = $contrato->venta()->with(['cliente', 'lote'])->first();
        $empresa = Empresa::first(); // O la que corresponda
        $cliente = $venta->cliente;
        $lote = $venta->lote;
        return view('contratos.plantilla-html', compact('contrato', 'venta', 'empresa', 'cliente', 'lote'));

    }

    // En ContratoController.php

    private function generarContenidoHtml(Venta $venta)
    {
        // ✅ Obtener los modelos
        $empresa = \App\Models\Empresa::first();
        $cliente = $venta->cliente;
        $lote = $venta->lote;

        // ✅ Retornar la vista renderizada con los datos
        return view('contratos.plantilla-html', compact('venta', 'empresa', 'cliente', 'lote'))->render();
    }

}