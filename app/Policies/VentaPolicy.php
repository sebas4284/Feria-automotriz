<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

class VentaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || $venta->concesionario_vende_id === $user->concesionario_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Venta $venta): bool
    {
        return $this->view($user, $venta);
    }

    public function delete(User $user, Venta $venta): bool
    {
        return $this->view($user, $venta);
    }
}
