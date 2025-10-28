<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'nro_cuota',
        'fecha_pago',
        'saldo',
        'interes',
        'amortizacion',
        'cuota',
        'estado', // pendiente, pagado, vencido, etc.
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

}
