<?php

namespace App\Models;

use App\Models\Concerns\ScopedToConcesionario;
use Illuminate\Database\Eloquent\Model;

class LeadReassignment extends Model
{
    use ScopedToConcesionario;

    protected $fillable = [
        'lead_id',
        'from_concesionario_id',
        'to_concesionario_id',
        'reassigned_by',
        'motivo',
        'lote_id',
    ];

    public function concesionarioColumn(): string
    {
        return 'to_concesionario_id';
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function fromConcesionario()
    {
        return $this->belongsTo(Concesionario::class, 'from_concesionario_id');
    }

    public function toConcesionario()
    {
        return $this->belongsTo(Concesionario::class, 'to_concesionario_id');
    }

    public function reassignedBy()
    {
        return $this->belongsTo(User::class, 'reassigned_by');
    }
}
