<?php
namespace Database\Seeders;
use App\Models\EstadoLote;
use Illuminate\Database\Seeder;

class EstadoLoteSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['estado' => 'Disponible', 'color' => '#28a745'], // Verde
            ['estado' => 'Reservado', 'color' => '#ffc107'],  // Amarillo
            ['estado' => 'Vendido',    'color' => '#fd7e14'], // Anaranjado
            ['estado' => 'Bloqueado',  'color' => '#dc3545'], // Rojo
        ];

        foreach ($estados as $estado) {
            EstadoLote::firstOrCreate(['estado' => $estado['estado']], $estado);
        }
    }
}
