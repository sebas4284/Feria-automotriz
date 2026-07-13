<?php

namespace App\Models;

use App\Models\Concerns\ScopedToConcesionario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use ScopedToConcesionario;

    protected $fillable = [
        'meta_lead_id',
        'created_time',
        'ad_id',
        'ad_name',
        'adset_id',
        'adset_name',
        'campaign_id',
        'campaign_name',
        'form_id',
        'form_name',
        'is_organic',
        'platform',
        'actividad_economica',
        'monto_interes_aprobar',
        'email',
        'full_name',
        'phone_number',
        'meta_lead_status',
        'raw_payload',
        'concesionario_id',
        'asesor_comercial_id',
        'estado_gestion',
        'assigned_at',
        'observaciones',
    ];

    protected $casts = [
        'created_time' => 'datetime',
        'assigned_at' => 'datetime',
        'is_organic' => 'boolean',
        'raw_payload' => 'array',
    ];

    public function concesionario()
    {
        return $this->belongsTo(Concesionario::class);
    }

    public function asesorComercial()
    {
        return $this->belongsTo(AsesorComercial::class);
    }

    public function reassignments()
    {
        return $this->hasMany(LeadReassignment::class);
    }

    public function asesorColumn(): ?string
    {
        return 'asesor_comercial_id';
    }

    public function scopeVencido(Builder $query): Builder
    {
        return $query->whereIn('estado_gestion', ['Nuevo', 'Asignado'])
            ->whereNotNull('assigned_at')
            ->where('assigned_at', '<', now()->subHours((int) config('leads.staleness_hours')));
    }

    public function getVencidoAttribute(): bool
    {
        return in_array($this->estado_gestion, ['Nuevo', 'Asignado'], true)
            && $this->assigned_at !== null
            && $this->assigned_at->lt(now()->subHours((int) config('leads.staleness_hours')));
    }

    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? ltrim(preg_replace('/^p:\s*/i', '', $value)) : $value,
            set: fn (?string $value) => $value ? ltrim(preg_replace('/^p:\s*/i', '', $value)) : $value,
        );
    }

    protected function whatsappUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $digits = preg_replace('/\D/', '', $this->phone_number ?? '');

                return $digits ? "https://wa.me/{$digits}" : null;
            },
        );
    }
}
