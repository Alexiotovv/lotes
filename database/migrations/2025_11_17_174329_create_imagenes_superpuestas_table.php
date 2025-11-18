<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('imagenes_superpuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_image_id')->constrained('map_images')->onDelete('cascade');
            $table->string('ruta_imagen'); // Ruta de la imagen en storage
            $table->decimal('lat_centro', 10, 8); // Latitud del centro de la imagen
            $table->decimal('lng_centro', 11, 8); // Longitud del centro de la imagen
            $table->decimal('ancho_lat', 10, 8); // Ancho vertical (en grados)
            $table->decimal('ancho_lng', 11, 8); // Ancho horizontal (en grados)
            $table->decimal('escala', 5, 4)->default(1.0000); // Escala de la imagen
            $table->decimal('opacidad', 3, 2)->default(0.85); // Opacidad (0.00 a 1.00)
            $table->boolean('activo')->default(true); // Si se muestra o no
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('imagenes_superpuestas');
    }
};