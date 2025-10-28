<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'tipo', 'categoria', 'activo'];
    
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}