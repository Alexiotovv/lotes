<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapImage extends Model
{
    protected $fillable = [
        'name',
        'ruta_imagen', // ✅ Nuevo nombre de campo
        'pos_x',
        'pos_y',
        'escala',
        'lat_map',
        'lon_map',
        'actual_zoom_map'
    ];

    protected $casts = [
        'pos_x' => 'decimal:2',
        'pos_y' => 'decimal:2',
        'escala' => 'decimal:4',
        'coordenadas' => 'array',
    ];
}