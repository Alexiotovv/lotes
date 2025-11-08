<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'area_m2',
        'coordenadas',
        'latitud',
        'longitud',
        'precio_m2',
        'estado',
        'descripcion',
        'estado_lote_id',
        'lado_izquierdo',
        'lado_derecho',
        'fondo',
        'frente',
    ];


    public function estadoLote()
    {
        return $this->belongsTo(EstadoLote::class, 'estado_lote_id');
    }
    
    public function venta()
    {
        return $this->hasOne(\App\Models\Venta::class, 'lote_id');
    }

}
