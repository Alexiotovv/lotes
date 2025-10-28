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
        'observaciones',
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


}
