<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Enums\UserRole;

class UserPolicy
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
        ]);
    }

    public function view(User $user, User $target): bool
    {
        return $this->scopeCheck($user, $target);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::DioceseAdmin->value,
            UserRole::SectorAdmin->value,
            UserRole::ParishAdmin->value,
        ]);
    }

    public function update(User $user, User $target): bool
    {
        return $this->create($user) && $this->scopeCheck($user, $target);
    }

    public function delete(User $user, User $target): bool
    {
        return $this->update($user, $target);
    }

    private function scopeCheck(User $user, User $target): bool
    {
        if ($user->hasRole(UserRole::ParishAdmin->value)) {
            return $user->parish_id === $target->parish_id;
        }

        if ($user->hasRole(UserRole::SectorAdmin->value)) {
            return $user->sector_id === $target->sector_id;
        }

        if ($user->hasRole(UserRole::DioceseAdmin->value)) {
            return $user->diocese_id === $target->diocese_id;
        }

        return false;
    }
}
