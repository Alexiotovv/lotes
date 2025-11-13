<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conceptos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->string('categoria')->nullable(); // Ej: "Ventas", "Servicios", "Personal"
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        
    }

    public function down()
    {
        Schema::dropIfExists('conceptos');
    }
};