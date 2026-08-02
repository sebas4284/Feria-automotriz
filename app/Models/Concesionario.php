<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Vehiculo;

class Concesionario extends Model
{
    protected $fillable = [
    'nombre',
    'sheet_tab_id',
    'nit',
    'ciudad',
    'direccion',
    'telefono',
    'email',
    'responsable',
    'peso_asignacion',
    'cupo_feria',
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

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}