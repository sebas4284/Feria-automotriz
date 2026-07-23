<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoCatalogo extends Model
{
    protected $table = 'vehiculo_catalogo';

    protected $fillable = [
        'marca',
        'linea',
        'version',
        'clase_vehiculo',
        'cc',
        'combustible',
        'transmision',
    ];
}
