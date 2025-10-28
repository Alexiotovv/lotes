<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50);
            $table->string('nombre', 100)->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->longText('coordenadas')->nullable(); //geojson
            $table->decimal('latitud', 12, 8)->nullable();
            $table->decimal('longitud', 12, 8)->nullable();
            $table->decimal('precio_m2', 10, 2)->nullable();
            $table->decimal('frente', 8, 2)->nullable();
            $table->decimal('lado_izquierdo', 8, 2)->nullable();
            $table->decimal('lado_derecho', 8, 2)->nullable();
            $table->decimal('fondo', 8, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
