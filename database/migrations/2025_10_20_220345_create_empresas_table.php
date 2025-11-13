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
            $table->string('departamento',50)->nullable();
            $table->string('provincia',50)->nullable();
            $table->string('distrito',50)->nullable();
            $table->string('telefono',50)->nullable();
            $table->string('logo')->nullable(); // Ruta de la imagen
            $table->timestamps();
        });

       
    }

    public function down()
    {
        Schema::dropIfExists('empresas');
    }
};