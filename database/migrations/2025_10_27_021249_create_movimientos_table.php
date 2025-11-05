<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained()->onDelete('restrict');
            $table->foreignId('concepto_id')->constrained()->onDelete('restrict');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->restrictOnDelete(); 
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('referencia')->nullable(); // N° operación, recibo, etc.
            $table->decimal('monto', 12, 2);
            $table->enum('tipo', ['ingreso', 'egreso']); // Derivado del concepto, pero útil para consultas
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->string('comprobante')->nullable(); // Ruta del voucher
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos');
    }
};