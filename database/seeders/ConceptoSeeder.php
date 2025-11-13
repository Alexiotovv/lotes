<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Conceptos predeterminados
        DB::table('conceptos')->insert([
            ['nombre' => 'Venta de Lote', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reserva de Lote', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pago inicial', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuota de financiamiento', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Servicios públicos', 'tipo' => 'egreso', 'categoria' => 'Operación', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sueldos', 'tipo' => 'egreso', 'categoria' => 'Personal', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Compra de Terreno', 'tipo' => 'egreso', 'categoria' => 'Adquisiciones', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otras Compras', 'tipo' => 'egreso', 'categoria' => 'Compras', 'created_at' => now(), 'updated_at' => now()],
        ]);


    }
}

















