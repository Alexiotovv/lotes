<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');  // Ej: "TEA"
            $table->decimal('monto', 8, 6);  // Ej: 0.120000 (12%)
            $table->timestamps();
        });

        // Opcional: Insertar TEA por defecto
        DB::table('tasas')->insert([
            'nombre' => 'TEA',
            'monto' => 0.12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('tasas');
    }
};