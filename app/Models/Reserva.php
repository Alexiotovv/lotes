<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'user_id',
        'cliente_id',
        'lote_id',
        'caja_id',
        'monto',
        'fecha_reserva',
        'observaciones',
    ];

    protected $casts = [
        // 'fecha_reserva' => 'date',
        'monto' => 'decimal:2',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }
}