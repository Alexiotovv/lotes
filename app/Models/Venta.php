<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cliente_id',
        'lote_id',
        'metodopago_id',
        'fecha_pago',
        'numero_cuotas',
        'inicial',
        'monto_financiar',
        'tasa_interes',
        'cuota',
        'cronograma_generado',
        'observaciones',
        'cronograma_generado',
        'estado',
    ];
    
    public function isFinalizada(): bool
    {
        if ($this->estado === 'contado') {
            return true;
        }
        
        return $this->cronogramas->every(fn($c) => $c->estado === 'pagado');
    }


    protected $casts = [
        'fecha_pago' => 'date',
    ];
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function metodopago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function cronogramas()
    {
        return $this->hasMany(Cronograma::class);
    }
    
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
    

    public function pagos()
    {
        return $this->hasManyThrough(Pago::class, Cronograma::class, 'venta_id', 'cronograma_id');
    }
    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }
    
    public function cronogramasAgrupados()
    {
        return $this->belongsToMany(
            CronogramaAgrupado::class, 
            'cronograma_agrupado_ventas', // nombre de la tabla pivote
            'venta_id',                    // foreign key en la tabla pivote
            'cronograma_agrupado_id'       // related key en la tabla pivote
        )->withPivot('monto_asignado')
         ->withTimestamps();
    }
}
