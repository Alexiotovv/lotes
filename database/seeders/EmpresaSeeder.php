<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use DB;
class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
         // Opcional: Insertar empresa inicial
        DB::table('empresas')->insert([
            'nombre' => 'Empresa Predeterminada',
            'ruc' => '12345678901',
            'direccion' => 'Av. Principal 123',
            'descripcion' => 'Empresa dedicada a la venta de terrenos.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
