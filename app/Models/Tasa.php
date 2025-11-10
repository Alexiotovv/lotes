<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasa extends Model
{
    protected $fillable = [
        'nombre',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:4',
    ];
}