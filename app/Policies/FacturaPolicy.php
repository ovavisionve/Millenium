<?php

namespace App\Policies;

use App\Models\Factura;
use App\Models\User;

class FacturaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Factura $factura): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->puedeGestionOperativaCompleta()) {
            return true;
        }

        if ($user->esVendedorRestringido()) {
            return (int) $factura->vendedor_id === (int) $user->id;
        }

        return false;
    }

    public function viewPagos(User $user, Factura $factura): bool
    {
        if (! $this->view($user, $factura)) {
            return false;
        }

        return $user->puedeGestionOperativaCompleta();
    }

    public function create(User $user): bool
    {
        return $user->puedeGestionOperativaCompleta();
    }

    public function update(User $user, Factura $factura): bool
    {
        return $user->puedeGestionOperativaCompleta();
    }

    public function delete(User $user, Factura $factura): bool
    {
        return $user->puedeGestionOperativaCompleta();
    }

    public function verificar(User $user, Factura $factura): bool
    {
        if (! $user->is_active || $factura->estaVerificada()) {
            return false;
        }

        if ($user->puedeGestionOperativaCompleta()) {
            return true;
        }

        if ($user->esVendedorRestringido()) {
            return (int) $factura->vendedor_id === (int) $user->id;
        }

        return false;
    }
}
