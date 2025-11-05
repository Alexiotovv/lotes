<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('configuraciongeneral', function (Blueprint $table) {
            $table->decimal('monto_compra_lote', 10, 2)->default(0.00)->after('monto_reserva_default');
        });

        // Actualizar el registro existente
        \App\Models\ConfiguracionGeneral::first()?->update(['monto_compra_lote' => 0.00]);
    }

    public function down()
    {
        Schema::table('configuraciongeneral', function (Blueprint $table) {
            $table->dropColumn('monto_compra_lote');
        });
    }
};