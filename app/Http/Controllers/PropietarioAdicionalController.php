<?php

namespace App\Http\Controllers;

use App\Models\PropietarioAdicional;
use Illuminate\Http\Request;

class PropietarioAdicionalController extends Controller
{
    public function destroy(PropietarioAdicional $propietarioAdicional)
    {
        $ventaId = $propietarioAdicional->venta_id;
        $propietarioAdicional->delete();

        return response()->json([
            'success' => true,
            'message' => 'Propietario adicional eliminado correctamente.',
            'venta_id' => $ventaId
        ]);
    }
}