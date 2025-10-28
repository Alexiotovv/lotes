<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruc', 11)->unique();
            $table->string('direccion');
            $table->text('descripcion')->nullable();
            $table->string('logo')->nullable(); // Ruta de la imagen
            $table->timestamps();
        });

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

    public function down()
    {
        Schema::dropIfExists('empresas');
    }
};