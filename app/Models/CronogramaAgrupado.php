<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronogramaAgrupado extends Model
{
    use HasFactory;

    protected $table = 'cronogramas_agrupados'; // Nombre correcto de la tabla
    
    protected $fillable = [
        'cliente_id',
        'cronograma_grupo_id', // ← NUEVO CAMPO
        'nro_cuota',
        'fecha_pago',
        'saldo',
        'interes',
        'amortizacion',
        'cuota',
        'estado'
    ];

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación con ventas
    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'cronograma_agrupado_ventas')
                    ->withPivot('monto_asignado')
                    ->withTimestamps();
    }

    // ✅ NUEVO: Relación para obtener todas las cuotas del mismo grupo
    public function grupo()
    {
        return $this->where('cronograma_grupo_id', $this->cronograma_grupo_id);
    }

    // ✅ NUEVO: Método para obtener el cronograma completo
    public function cronogramaCompleto()
    {
        return CronogramaAgrupado::where('cronograma_grupo_id', $this->cronograma_grupo_id)
            ->orderBy('nro_cuota')
            ->get();
    }
}