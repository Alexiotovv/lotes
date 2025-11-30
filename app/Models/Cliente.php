<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'dni_ruc',
        'nombre_cliente',
        'genero',
        'direccion',
        'departamento',
        'provincia',
        'distrito',
        'telefono',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
