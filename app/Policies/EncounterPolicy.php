<?php

namespace App\Policies;

use App\Domain\Encounter\Models\Encounter;
use App\Models\User;
use App\Support\Enums\UserRole;

class EncounterPolicy
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

    public function view(User $user, Encounter $encounter): bool
    {
        if ($user->parish_id !== $encounter->parish_id) {
            return false;
        }

        // Coordinators must be assigned to the encounter's movement
        if ($user->hasRole(UserRole::Coordinator->value)) {
            return $user->movements()->where('movements.id', $encounter->movement_id)->exists();
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ParishAdmin->value,
            UserRole::Coordinator->value,
        ]);
    }

    public function update(User $user, Encounter $encounter): bool
    {
        if ($user->parish_id !== $encounter->parish_id) {
            return false;
        }

        if ($user->hasRole(UserRole::Coordinator->value)) {
            return $user->movements()->where('movements.id', $encounter->movement_id)->exists();
        }

        return $this->create($user);
    }

    public function delete(User $user, Encounter $encounter): bool
    {
        return $user->hasRole(UserRole::ParishAdmin->value)
            && $user->parish_id === $encounter->parish_id;
    }
}
