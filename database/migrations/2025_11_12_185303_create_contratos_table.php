<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Quién generó el contrato
            $table->text('contenido_html'); // Contenido del contrato en HTML
            $table->boolean('activo')->default(true); // Para manejar anulaciones
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratos');
    }
};