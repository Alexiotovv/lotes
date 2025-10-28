<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLotes = \App\Models\Lote::count();
        $vendidos = \App\Models\Lote::whereHas('estadoLote', function ($q) {
            $q->where('estado', 'Vendido'); 
        })->count();
        $ingresos = \App\Models\Venta::sum('monto_financiar');
        return view('dashboard', compact('totalLotes', 'vendidos', 'ingresos'));
    }
}
