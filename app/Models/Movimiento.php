<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;
    protected $fillable = [
        'caja_id',
        'concepto_id',
        'venta_id',
        'user_id',
        'referencia',
        'monto',
        'tipo',
        'fecha',
        'descripcion',
        'comprobante'
    ];
    
    protected $casts = [
        'fecha' => 'date',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function concepto()
    {
        return $this->belongsTo(Concepto::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}