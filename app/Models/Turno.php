<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = [
        'concesionario_id',
        'fecha',
        'llegada_at',
        'ultima_asignacion_at',
        'ultima_asignacion_at_previa',
        'tiene_asignacion_deshacible',
        'veces_asignado',
        'veces_procesado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'llegada_at' => 'datetime',
        'ultima_asignacion_at' => 'datetime',
        'ultima_asignacion_at_previa' => 'datetime',
        'tiene_asignacion_deshacible' => 'boolean',
    ];

    public function concesionario()
    {
        return $this->belongsTo(Concesionario::class);
    }
}
