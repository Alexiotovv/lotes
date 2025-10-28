<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            // Agregamos el campo como nullable para no romper registros existentes
            $table->foreignId('estado_lote_id')->nullable()
                  ->constrained('estado_lotes')
                  ->nullOnDelete(); // Si se borra el estado, el campo quedará NULL
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['estado_lote_id']);
            $table->dropColumn('estado_lote_id');
        });
    }
};
