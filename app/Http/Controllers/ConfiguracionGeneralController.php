<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionGeneral;
use Illuminate\Http\Request;

class ConfiguracionGeneralController extends Controller
{
    public function edit()
    {
        $config = ConfiguracionGeneral::first();
        return view('configuracion.edit', compact('config'));
    }

    // app/Http/Controllers/ConfiguracionGeneralController.php

    public function update(Request $request)
    {
        // ✅ Paso 1: Incluir 'monto_compra_lote' en la lista de campos permitidos
        $request->validate([
            'campo' => 'required|in:monto_reserva_default,registrar_lote_compra,monto_compra_lote',
            'valor' => 'required',
        ]);

        $config = ConfiguracionGeneral::first();

        if ($request->campo === 'monto_reserva_default') {
            $request->validate(['valor' => 'numeric|min:0']);
            $config->monto_reserva_default = $request->valor;
        } elseif ($request->campo === 'registrar_lote_compra') {
            $config->registrar_lote_compra = (bool) $request->valor;
        } 
        // ✅ Paso 2: Agregar el nuevo bloque aquí
        elseif ($request->campo === 'monto_compra_lote') {
            $request->validate(['valor' => 'numeric|min:0']);
            $config->monto_compra_lote = $request->valor;
        }

        $config->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente.'
        ]);
    }
}