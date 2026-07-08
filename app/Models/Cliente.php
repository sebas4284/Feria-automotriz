<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [

        'nombre',
        'telefono',
        'email',
        'ciudad',
        'vehiculo_interes',
        'presupuesto',
        'estado',
        'observaciones',
        'user_id'
    ];
    public function ventas()
{
    return $this->hasMany(Venta::class);
}
}