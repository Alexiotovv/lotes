<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;
    protected $table = 'cotizaciones'; 
    protected $fillable = [
        'user_id',
        'cliente_id',
        'metodopago_id',
        'fecha_pago',
        'numero_cuotas',
        'inicial',
        'lote_id',
        'tasa_id',
        'cuota',
        'tasa_interes',
        'observaciones',
        'monto_financiar',
    ];

    public function lote() { return $this->belongsTo(Lote::class); }
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function metodopago() { return $this->belongsTo(MetodoPago::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function tasa() { return $this->belongsTo(Tasa::class); }

}
