<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuraciongeneral', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto_reserva_default', 10, 2)->default(200.00);
            $table->boolean('registrar_lote_compra')->default(false);
            $table->decimal('monto_compra_lote', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // Insertar registro inicial
        DB::table('configuraciongeneral')->insert([
            'monto_reserva_default' => 200.00,
            'registrar_lote_compra' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('configuraciongeneral');
    }
};