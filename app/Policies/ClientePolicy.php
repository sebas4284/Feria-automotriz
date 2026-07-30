<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->isAdmin() || $user->isStaff() || $cliente->concesionario_id === $user->concesionario_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Cliente $cliente): bool
    {
        if ($user->isStaff()) {
            return false;
        }

        return $user->isAdmin() || $cliente->concesionario_id === $user->concesionario_id;
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $this->update($user, $cliente);
    }
}
