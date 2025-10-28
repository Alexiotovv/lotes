<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clientes')->insert([
            [
                'dni_ruc' => '12345678',
                'nombre_cliente' => 'Juan Pérez Gómez',
                'genero' => 'Masculino',
                'direccion' => 'Av. Los Olivos 123',
                'departamento' => 'Lima',
                'provincia' => 'Lima',
                'distrito' => 'San Martín de Porres',
                'telefono' => '987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
