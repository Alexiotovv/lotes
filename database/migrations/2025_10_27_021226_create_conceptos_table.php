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

        // Conceptos predeterminados
        DB::table('conceptos')->insert([
            ['nombre' => 'Venta de Lote', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reserva de Lote', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pago inicial', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cuota de financiamiento', 'tipo' => 'ingreso', 'categoria' => 'Ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Servicios públicos', 'tipo' => 'egreso', 'categoria' => 'Operación', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sueldos', 'tipo' => 'egreso', 'categoria' => 'Personal', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Compra de Terreno', 'tipo' => 'egreso', 'categoria' => 'Adquisiciones', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Otras Compras', 'tipo' => 'egreso', 'categoria' => 'Adquisiciones', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('conceptos');
    }
};