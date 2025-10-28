<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // quien registró
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('cascade');
            $table->foreignId('metodopago_id')->constrained('metodopagos')->onDelete('cascade');
            $table->date('fecha_pago');
            $table->integer('numero_cuotas')->nullable();
            $table->decimal('monto_financiar', 10, 2)->default(0);
            $table->decimal('tasa_interes', 10, 2)->default(0);
            $table->decimal('inicial', 10, 2)->default(0);
            $table->decimal('cuota', 12, 2)->nullable();
            $table->string('observaciones', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cotizaciones');
    }
};
