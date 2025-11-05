<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionGeneral extends Model
{
    protected $table = 'configuraciongeneral';

    protected $fillable = [
        'monto_reserva_default',
        'registrar_lote_compra',
        'monto_compra_lote',
    ];

    protected $casts = [
        'registrar_lote_compra' => 'boolean',
    ];
}