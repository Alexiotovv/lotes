<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'tipo', 'saldo_inicial', 'activo'];
    
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}