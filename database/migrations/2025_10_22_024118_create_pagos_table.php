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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained('cronogramas')->onDelete('restrict');
            $table->date('fecha_pago');
            $table->decimal('monto_pagado', 12, 2);
            $table->string('metodo_pago', 50)->nullable();
            $table->string('referencia', 100)->nullable(); // Nº operación, recibo, etc.
            $table->text('observacion')->nullable();
            $table->string('voucher')->nullable();// ruta de la imagen en storage
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
