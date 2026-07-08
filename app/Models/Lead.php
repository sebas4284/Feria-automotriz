<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [

        'meta_lead_id',
        'nombre',
        'telefono',
        'email',
        'ciudad',
        'vehiculo_interes',

        'estado',

        'concesionario_id',

        'observacion',

        'fecha_asignacion',

        'ultima_gestion',

        'reasignaciones',

        'fuente',
    ];

    public function concesionario()
    {
        return $this->belongsTo(
            Concesionario::class
        );
    }
}