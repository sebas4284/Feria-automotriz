<?php

namespace App\Models;


use App\Models\Concerns\ScopedToConcesionario;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use ScopedToConcesionario;

    protected $fillable = [

        'nombre',
        'telefono',
        'cita',
        'concesionario_id',
        'oculto_en_turnos',
        'medio_entero',
        'observaciones',
        'user_id'
    ];

    protected $casts = [
        'cita' => 'boolean',
        'oculto_en_turnos' => 'boolean',
    ];

    public function ventas()
{
    return $this->hasMany(Venta::class);
}

    public function concesionario()
    {
        return $this->belongsTo(Concesionario::class);
    }
}