<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('map_images', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // nombre descriptivo
            $table->string('ruta_imagen')->nullable(); // Ruta de la imagen original
            $table->decimal('pos_x', 10, 2)->default(0); // Desplazamiento X
            $table->decimal('pos_y', 10, 2)->default(0); // Desplazamiento Y
            $table->decimal('escala', 5, 4)->default(1.0000); // Escala de la imagen
            $table->decimal('lat_map',10,8)->default(-3.844051)->nullable();
            $table->decimal('lon_map',10,8)->default(-73.3432986)->nullable();
            $table->tinyInteger('max_zoom_map')->default(19);
            $table->tinyInteger('min_zoom_map')->default(15);
            $table->tinyInteger('actual_zoom_map')->default(19);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('map_images');
    }
};