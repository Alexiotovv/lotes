<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict');
            $table->integer('nro_cuota');                  // Número de cuota
            $table->date('fecha_pago');                    // Fecha programada de pago
            $table->decimal('saldo', 12, 2);               // Saldo antes del pago
            $table->decimal('interes', 12, 2)->default(0); // Interés calculado
            $table->decimal('amortizacion', 12, 2)->default(0); // Amortización
            $table->decimal('cuota', 12, 2)->default(0);   // Monto total de la cuota
            $table->enum('estado', ['pendiente', 'pagado', 'vencido'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronogramas');
    }
};
