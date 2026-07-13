<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Concesionario;

class Vehiculo extends Model
{
    protected $fillable = [

        'concesionario_id',

        'placa',

        'numero_llave',

        'foto',

        'marca',

        'linea',

        'version',

        'modelo',

        'color',

        'clase_vehiculo',

        'tipo_vehiculo',

        'cc',

        'combustible',

        'transmision',

        'kilometraje',

        'fecha_matricula',

        'ciudad_matricula',

        'fecha_soat',

        'fecha_tecno',

        'impuestos',

        'accesorios',

        'cod_fasecolda',

        'pr_fasecolda',

        'precio_normal',

        'bono_descuento',

        'precio_expocar',

        'estado'
    ];
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::disk('public')->url($this->foto) : null;
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
    public function concesionario()
{
    return $this->belongsTo(
        Concesionario::class
    );
}
}