<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['efectivo', 'banco', 'digital']);
            $table->decimal('saldo_inicial', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        
    }

    public function down()
    {
        Schema::dropIfExists('cajas');
    }
};