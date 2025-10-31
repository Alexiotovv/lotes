<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('metodopagos', function (Blueprint $table) {
            $table->boolean('es_credito')->default(false)->after('descripcion');
        });
    }

    public function down()
    {
        Schema::table('metodopagos', function (Blueprint $table) {
            $table->dropColumn('es_credito');
        });
    }
};