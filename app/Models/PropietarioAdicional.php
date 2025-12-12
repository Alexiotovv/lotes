<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropietarioAdicional extends Model
{
    use HasFactory;

    protected $fillable = ['venta_id', 'cliente_id'];

    // Relación con venta
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}