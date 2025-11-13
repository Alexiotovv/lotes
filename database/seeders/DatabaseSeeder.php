<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\EstadoLoteSeeder;
use Database\Seeders\ClienteSeeder;
use Database\Seeders\MetodoPagoSeeder;
use Database\Seeders\TasaSeeder;
use Database\Seeders\EmpresaSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([UserSeeder::class,]);
        $this->call([EstadoLoteSeeder::class,]);
        $this->call([clienteSeeder::class,]);
        $this->call([MetodoPagoSeeder::class,]);
        $this->call([TasaSeeder::class,]);
        $this->call([EmpresaSeeder::class,]);
    }
}
