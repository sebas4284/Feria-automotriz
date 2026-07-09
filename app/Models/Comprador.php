<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprador extends Model
{
    protected $table = 'compradores';

    protected $fillable = [
        'identificacion',
        'nombre',
        'telefono',
        'direccion',
        'correo',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
