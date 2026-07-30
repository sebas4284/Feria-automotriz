<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopedToConcesionario
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return $query;
        }

        if ($user->isAsesor() && $this->asesorColumn()) {
            return $query->where($this->asesorColumn(), $user->asesor_comercial_id);
        }

        return $query->where($this->concesionarioColumn(), $user->concesionario_id);
    }

    public function concesionarioColumn(): string
    {
        return 'concesionario_id';
    }

    public function asesorColumn(): ?string
    {
        return null;
    }
}
