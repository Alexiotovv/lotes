<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@lotizacion.com',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@lotizacion.com',
            'password' => bcrypt('password123'),
            'role' => 'vendedor'
        ]);
    }
}