<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesorComercial extends Model
{
    protected $table = 'asesores_comerciales';

    protected $fillable = [
        'cedula',
        'nombre',
        'telefono',
        'concesionario_id',
    ];

    public function concesionario()
    {
        return $this->belongsTo(Concesionario::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
