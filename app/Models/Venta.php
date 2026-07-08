<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'cliente_id',
        'vehiculo_id',
        'user_id',
        'valor',
        'fecha_venta',
        'forma_pago',
        'observaciones'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function concesionario()
{
    return $this->belongsTo(
        Concesionario::class
    );
}
}