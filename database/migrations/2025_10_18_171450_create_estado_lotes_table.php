<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_lotes', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 50)->unique();
            $table->string('color', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_lotes');
    }
};
