<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapImage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image_path', 'position'];

    protected $casts = [
        'position' => 'array',
    ];
}
