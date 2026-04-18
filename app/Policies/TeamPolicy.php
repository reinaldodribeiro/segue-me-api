<?php

namespace App\Policies;

use App\Domain\Encounter\Models\Team;
use App\Models\User;
use App\Support\Enums\UserRole;

class TeamPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole(UserRole::SuperAdmin->value)) {
            return true;
        }

        return null;
    }

    public function manage(User $user, Team $team): bool
    {
        // withoutGlobalScopes é necessário aqui: a policy precisa ler o dado real
        // para comparar parish_id, independente do escopo da sessão autenticada.
        $encounter = $team->encounter()->withoutGlobalScopes()->first();

        if (! $encounter) {
            return false;
        }

        return $user->hasAnyRole([UserRole::ParishAdmin->value, UserRole::Coordinator->value])
            && $user->parish_id === $encounter->parish_id;
    }
}
