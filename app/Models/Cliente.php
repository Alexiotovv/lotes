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
    public function cronogramasAgrupados()
    {
        return $this->hasManyThrough(
            Cronograma::class,
            Venta::class,
            'cliente_id', // Foreign key en la tabla ventas
            'venta_id',   // Foreign key en la tabla cronogramas
            'id',         // Local key en la tabla clientes
            'id'          // Local key en la tabla ventas
        )->whereNotNull('cronogramas.grupo_id') // Solo cronogramas con grupo_id
        ->select('cronogramas.*')
        ->groupBy('cronogramas.grupo_id'); // Agrupar por grupo_id
    }

        // ✅ Método para obtener grupos únicos de cronogramas
    public function gruposCronogramas()
    {
        return Cronograma::whereHas('venta', function($q) {
                $q->where('cliente_id', $this->id);
            })
            ->whereNotNull('grupo_id')
            ->select('grupo_id')
            ->distinct()
            ->get()
            ->map(function($item) {
                return $item->grupo_id;
            });
    }

    // En App\Models\Cliente.php
    public function ventasComoPropietarioAdicional()
    {
        return $this->belongsToMany(Venta::class, 'propietarios_adicionales')
                    ->withTimestamps();
    }

    
}
