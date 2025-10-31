<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metodopago extends Model
{
    use HasFactory;

    protected $table = 'metodopagos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'es_credito',
    ];
    protected $casts = [
        'es_credito' => 'boolean',
        'activo' => 'boolean',
    ];

    public function metodoPago()
    {
        return $this->belongsTo(\App\Models\MetodoPago::class, 'metodopago_id');
    }
}
