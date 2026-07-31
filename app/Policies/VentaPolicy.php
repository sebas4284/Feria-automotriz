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
        return $user->isAdmin()
            || $user->isAseguradora()
            || $this->esConcesionarioVendedor($user, $venta)
            || $venta->vehiculo?->concesionario_id === $user->concesionario_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || $this->esConcesionarioVendedor($user, $venta);
    }

    public function delete(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || $this->esConcesionarioVendedor($user, $venta);
    }

    private function esConcesionarioVendedor(User $user, Venta $venta): bool
    {
        return $venta->concesionario_vende_id === $user->concesionario_id;
    }
}
