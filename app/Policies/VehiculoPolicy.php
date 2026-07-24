<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehiculo;

class VehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Vehiculo $vehiculo): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->concesionarioIdPropio() !== null;
    }

    public function update(User $user, Vehiculo $vehiculo): bool
    {
        return $user->isAdmin() || $vehiculo->concesionario_id === $user->concesionarioIdPropio();
    }

    public function delete(User $user, Vehiculo $vehiculo): bool
    {
        return $this->update($user, $vehiculo);
    }
}
