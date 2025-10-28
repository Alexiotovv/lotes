<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('map_images', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // nombre descriptivo
            $table->string('image_path'); // ruta del archivo
            $table->json('position')->nullable(); // coordenadas JSON de corners
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_images');
    }
};
