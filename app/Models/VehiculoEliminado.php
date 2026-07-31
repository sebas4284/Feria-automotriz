<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoEliminado extends Model
{
    protected $table = 'vehiculos_eliminados';

    const UPDATED_AT = null;

    protected $fillable = [
        'vehiculo_id',
        'placa',
        'marca',
        'linea',
        'modelo',
        'concesionario_nombre',
        'precio_expocar',
        'kilometraje',
        'estado',
        'datos',
        'eliminado_por',
        'eliminado_por_nombre',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'eliminado_por');
    }
}
