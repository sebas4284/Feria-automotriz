<?php

namespace App\Models;

use App\Models\Concerns\ScopedToConcesionario;
use Illuminate\Database\Eloquent\Builder;
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
        'banco',
        'tiene_retoma',
        'retoma_valor',
        'retoma_descripcion',
        'observaciones',
        'participa_experiencia',
        'detalle_experiencia',
    ];

    protected $casts = [
        'participa_experiencia' => 'boolean',
        'tiene_retoma' => 'boolean',
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
        return 'EXP-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function concesionarioColumn(): string
    {
        return 'concesionario_vende_id';
    }

    /**
     * Además de las ventas que gestionó el concesionario, incluye las
     * ventas cruzadas: cuando otro concesionario vendió un vehículo de
     * su propio inventario (ej. venta en feria de un auto ajeno).
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isStaff() || $user->isAseguradora()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('concesionario_vende_id', $user->concesionario_id)
                ->orWhereHas('vehiculo', fn (Builder $vq) => $vq->where('concesionario_id', $user->concesionario_id));
        });
    }
}
