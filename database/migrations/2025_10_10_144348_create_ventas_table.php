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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('metodopago_id')->constrained('metodopagos')->onDelete('restrict');
            // Datos de la venta
            $table->date('fecha_pago');                     // Fecha de inicio del cronograma
            $table->integer('numero_cuotas');               // Total de cuotas
            $table->decimal('inicial', 12, 2)->default(0);  // Pago inicial
            $table->decimal('monto_financiar', 12, 2)->default(0); // Monto a financiar
            $table->decimal('tasa_interes', 5, 4)->default(0); // Tasa de interés (ejemplo: 0.02 = 2%)
            $table->decimal('cuota', 12, 2)->default(0);    // Monto de la cuota mensual
            $table->text('observaciones')->nullable();      //comentarios
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
