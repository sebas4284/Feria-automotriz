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
        'tipo_documento',
        'lugar_expedicion',
        'fecha_expedicion',
    ];

    protected $casts = [
        'fecha_expedicion' => 'date',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
