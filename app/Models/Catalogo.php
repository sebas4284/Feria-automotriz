<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $fillable = [
        'tipo',
        'valor',
    ];

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}
