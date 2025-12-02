<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cronograma extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'grupo_id', // ← NUEVO CAMPO
        'nro_cuota',
        'fecha_pago',
        'saldo',
        'interes',
        'amortizacion',
        'cuota',
        'estado'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'saldo' => 'decimal:2',
        'interes' => 'decimal:2',
        'amortizacion' => 'decimal:2',
        'cuota' => 'decimal:2',
    ];

    // Relación con venta
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación con cliente a través de venta
    public function cliente()
    {
        return $this->venta->cliente();
    }

    // Relación con pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // ✅ NUEVO: Método para obtener cronogramas del mismo grupo
    public function grupo()
    {
        return self::where('grupo_id', $this->grupo_id);
    }

    // ✅ NUEVO: Accessor para estado del grupo completo
    public function getEstadoGrupoAttribute()
    {
        if (!$this->grupo_id) return 'individual';
        
        $grupo = self::where('grupo_id', $this->grupo_id)->get();
        $total = $grupo->count();
        $pagados = $grupo->where('estado', 'pagado')->count();
        $vencidos = $grupo->where('estado', 'vencido')->count();
        
        if ($pagados === $total) return 'pagado';
        if ($vencidos > 0) return 'vencido';
        if ($pagados > 0) return 'parcial';
        return 'pendiente';
    }

    // ✅ NUEVO: Accessor para obtener ventas del mismo grupo
    public function getVentasGrupoAttribute()
    {
        if (!$this->grupo_id) return collect([$this->venta]);
        
        $ventasIds = self::where('grupo_id', $this->grupo_id)
            ->pluck('venta_id')
            ->unique();
            
        return Venta::whereIn('id', $ventasIds)->with('lote')->get();
    }
}