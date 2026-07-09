<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vehiculo;

class Concesionario extends Model
{
    protected $fillable = [
    'nombre',
    'nit',
    'ciudad',
    'telefono',
    'email',
    'responsable',
    'peso_asignacion',
    'activo'
];
protected $casts = [
    'activo' => 'boolean',
];
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
    public function vehiculos()
{
    return $this->hasMany(Vehiculo::class);
}
}