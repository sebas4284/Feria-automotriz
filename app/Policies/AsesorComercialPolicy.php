<?php

namespace App\Policies;

use App\Models\AsesorComercial;
use App\Models\User;

class AsesorComercialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AsesorComercial $asesor): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AsesorComercial $asesor): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AsesorComercial $asesor): bool
    {
        return $user->isAdmin();
    }
}
