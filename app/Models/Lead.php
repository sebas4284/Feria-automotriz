<?php

namespace App\Models;

use App\Models\Concerns\ScopedToConcesionario;
use Illuminate\Database\Eloquent\Builder;
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

    public function reassignments()
    {
        return $this->hasMany(LeadReassignment::class);
    }

    public function scopeVencido(Builder $query): Builder
    {
        return $query->where('estado_gestion', 'Nuevo')
            ->whereNotNull('assigned_at')
            ->where('assigned_at', '<', now()->subHours((int) config('leads.staleness_hours')));
    }

    public function getVencidoAttribute(): bool
    {
        return $this->estado_gestion === 'Nuevo'
            && $this->assigned_at !== null
            && $this->assigned_at->lt(now()->subHours((int) config('leads.staleness_hours')));
    }
}
