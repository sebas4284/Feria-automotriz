<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isAsesor()) {
            return $lead->asesor_comercial_id === $user->asesor_comercial_id;
        }

        return $lead->concesionario_id === $user->concesionario_id;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }

    public function reassign(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }

    public function assignAsesor(User $user, Lead $lead): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isConcesionario() && $lead->concesionario_id === $user->concesionario_id;
    }
}
