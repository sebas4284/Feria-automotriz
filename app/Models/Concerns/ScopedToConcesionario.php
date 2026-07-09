<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopedToConcesionario
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where($this->concesionarioColumn(), $user->concesionario_id);
    }

    public function concesionarioColumn(): string
    {
        return 'concesionario_id';
    }
}
