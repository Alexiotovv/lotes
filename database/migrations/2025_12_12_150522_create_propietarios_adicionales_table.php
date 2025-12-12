<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('propietarios_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->timestamps();
            
            // Índice para evitar duplicados en la misma venta
            $table->unique(['venta_id', 'cliente_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('propietarios_adicionales');
    }
};