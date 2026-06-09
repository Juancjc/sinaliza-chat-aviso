<?php

namespace App\Policies;

use App\Models\Grupo;
use App\Models\User;

class GrupoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Grupo $grupo): bool
    {
        if ($user->isAdmin()) {
            return $this->owns($user, $grupo);
        }

        return $grupo->participantes()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Grupo $grupo): bool
    {
        return $this->owns($user, $grupo);
    }

    public function delete(User $user, Grupo $grupo): bool
    {
        return $this->owns($user, $grupo);
    }

    public function manageParticipants(User $user, Grupo $grupo): bool
    {
        return $this->owns($user, $grupo);
    }

    public function sendAviso(User $user, Grupo $grupo): bool
    {
        return $this->owns($user, $grupo);
    }

    public function sendMessage(User $user, Grupo $grupo): bool
    {
        return $this->view($user, $grupo);
    }

    private function owns(User $user, Grupo $grupo): bool
    {
        return $user->isAdmin() && $grupo->user_id === $user->id;
    }
}
