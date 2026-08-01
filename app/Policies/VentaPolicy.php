<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;
use App\Models\Vehiculo;

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
            || $this->esDuenoDelVehiculo($user, $venta);
    }

    public function create(User $user, ?Vehiculo $vehiculo = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($vehiculo === null) {
            return true;
        }

        return $vehiculo->concesionario_id === $user->concesionarioIdPropio();
    }

    public function update(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || $this->esDuenoDelVehiculo($user, $venta);
    }

    public function delete(User $user, Venta $venta): bool
    {
        return $user->isAdmin() || $this->esDuenoDelVehiculo($user, $venta);
    }

    private function esConcesionarioVendedor(User $user, Venta $venta): bool
    {
        return $venta->concesionario_vende_id === $user->concesionario_id;
    }

    private function esDuenoDelVehiculo(User $user, Venta $venta): bool
    {
        return $venta->vehiculo?->concesionario_id === $user->concesionarioIdPropio();
    }
}
