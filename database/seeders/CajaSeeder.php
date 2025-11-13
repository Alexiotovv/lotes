<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use DB;
class CajaSeeder extends Seeder
{
    public function run(): void
    {
        // Caja predeterminada
        DB::table('cajas')->insert([
            'nombre' => 'Caja Principal',
            'tipo' => 'efectivo',
            'saldo_inicial' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}









