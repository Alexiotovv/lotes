<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->string('grupo_id', 100)->nullable()->after('venta_id');
            $table->index('grupo_id'); // Índice para búsquedas rápidas
        });
    }

    public function down()
    {
        Schema::table('cronogramas', function (Blueprint $table) {
            $table->dropIndex(['grupo_id']);
            $table->dropColumn('grupo_id');
        });
    }
};