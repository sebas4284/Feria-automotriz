<?php

namespace App\Models;

use App\Models\Concerns\ScopedToConcesionario;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use ScopedToConcesionario;

    protected $fillable = [
        'cliente_id',
        'comprador_id',
        'vehiculo_id',
        'concesionario_vende_id',
        'user_id',
        'asesor_comercial_id',
        'valor',
        'fecha_venta',
        'forma_pago',
        'observaciones',
        'participa_experiencia',
    ];

    protected $casts = [
        'participa_experiencia' => 'boolean',
        'fecha_venta' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function comprador()
    {
        return $this->belongsTo(Comprador::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function concesionarioVende()
    {
        return $this->belongsTo(Concesionario::class, 'concesionario_vende_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asesorComercial()
    {
        return $this->belongsTo(AsesorComercial::class);
    }

    public function getBoletaAttribute(): string
    {
        return 'RIFA-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function concesionarioColumn(): string
    {
        return 'concesionario_vende_id';
    }
}
