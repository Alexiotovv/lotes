<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'cronograma_id',
        'fecha_pago',
        'monto_pagado',
        'metodo_pago',
        'referencia',
        'voucher', 
        'observacion'
    ];

    public function cronograma()
    {
        return $this->belongsTo(Cronograma::class);
    }
}
