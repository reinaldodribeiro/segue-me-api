<?php

namespace App\Policies;

use App\Domain\Encounter\Models\Movement;
use App\Models\User;
use App\Support\Enums\UserRole;

class MovementPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole(UserRole::SuperAdmin->value)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::DioceseAdmin->value,
            UserRole::SectorAdmin->value,
            UserRole::ParishAdmin->value,
            UserRole::Coordinator->value,
        ]);
    }

    public function view(User $user, Movement $movement): bool
    {
        // Coordinators must be explicitly assigned to the movement
        if ($user->hasRole(UserRole::Coordinator->value)) {
            return $user->movements()->where('movements.id', $movement->id)->exists();
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ParishAdmin->value);
    }

    public function update(User $user, Movement $movement): bool
    {
        return $user->hasRole(UserRole::ParishAdmin->value);
    }

    public function delete(User $user, Movement $movement): bool
    {
        return $this->update($user, $movement);
    }
}
