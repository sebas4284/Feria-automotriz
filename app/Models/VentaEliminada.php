<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaEliminada extends Model
{
    protected $table = 'ventas_eliminadas';

    const UPDATED_AT = null;

    protected $fillable = [
        'venta_id',
        'vehiculo_placa',
        'vehiculo_marca',
        'vehiculo_modelo',
        'comprador_nombre',
        'concesionario_vende_nombre',
        'asesor_nombre',
        'valor',
        'fecha_venta',
        'motivo',
        'datos',
        'eliminado_por',
        'eliminado_por_nombre',
    ];

    protected $casts = [
        'datos' => 'array',
        'fecha_venta' => 'date',
    ];

    public function eliminadoPor()
    {
        return $this->belongsTo(User::class, 'eliminado_por');
    }
}
