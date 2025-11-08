<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    public function run()
    {
        // Evitar duplicados (por nombre)
        if (!MetodoPago::where('nombre', 'Crédito')->exists()) {
            MetodoPago::create([
                'nombre' => 'Crédito',
                'descripcion' => 'Venta al crédito con financiamiento',
                'activo' => true,
                'es_credito' => true,
            ]);
        }

        if (!MetodoPago::where('nombre', 'Contado')->exists()) {
            MetodoPago::create([
                'nombre' => 'Contado',
                'descripcion' => 'Venta al contado, sin financiamiento',
                'activo' => true,
                'es_credito' => false,
            ]);
        }

        // if (!MetodoPago::where('nombre', 'Reserva')->exists()) {
        //     MetodoPago::create([
        //         'nombre' => 'Reserva',
        //         'descripcion' => 'Venta al crédito con reserva',
        //         'activo' => true,
        //         'es_credito' => true,
        //     ]);
        // }
        
    }
}