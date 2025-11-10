<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tasas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');  // Ej: "TEA"
            $table->decimal('monto', 8, 6);  // Ej: 0.120000 (12%)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasas');
    }
};