<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasaSeeder extends Seeder
{
    public function run()
    {
        $tasas = [
            [
                'nombre' => 'TEA 0%',
                'monto' => 0.00,
            ],
            [
                'nombre' => 'TEA 12%',
                'monto' => 0.12,
            ],
            [
                'nombre' => 'TEA 15%',
                'monto' => 0.15,
            ],
            [
                'nombre' => 'TEA 18%',
                'monto' => 0.18,
            ],
        ];

        foreach ($tasas as $tasa) {
            DB::table('tasas')->updateOrInsert(
                ['monto' => $tasa['monto']], // campo único para evitar duplicados
                [
                    'nombre' => $tasa['nombre'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}