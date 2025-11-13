<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $fillable = [
        'venta_id',
        'user_id',
        'contenido_html',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}