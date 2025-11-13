<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('lote_id')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('restrict');
            $table->decimal('monto', 12, 2); // monto de la reserva
            $table->date('fecha_reserva');
            $table->text('observaciones')->nullable();
            $table->string('voucher')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservas');
    }
};