<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenSuperpuesta extends Model
{
    protected $table = 'imagenes_superpuestas';

    protected $fillable = [
        'map_image_id',
        'ruta_imagen',
        'lat_centro',
        'lng_centro',
        'ancho_lat',
        'ancho_lng',
        'escala',
        'opacidad',
        'activo',
    ];

    protected $casts = [
        'lat_centro' => 'decimal:8',
        'lng_centro' => 'decimal:8',
        'ancho_lat' => 'decimal:8',
        'ancho_lng' => 'decimal:8',
        'escala' => 'decimal:4',
        'opacidad' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function mapImage()
    {
        return $this->belongsTo(MapImage::class);
    }
}