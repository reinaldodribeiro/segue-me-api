<?php

namespace App\Policies;

use App\Domain\People\Models\Person;
use App\Models\User;
use App\Support\Enums\UserRole;

class PersonPolicy
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
            UserRole::ParishAdmin->value,
            UserRole::Coordinator->value,
        ]);
    }

    public function view(User $user, Person $person): bool
    {
        return $this->viewAny($user) && $user->parish_id === $person->parish_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRole::ParishAdmin->value,
            UserRole::Coordinator->value,
        ]);
    }

    public function update(User $user, Person $person): bool
    {
        return $this->create($user) && $user->parish_id === $person->parish_id;
    }

    public function delete(User $user, Person $person): bool
    {
        return $user->hasRole(UserRole::ParishAdmin->value)
            && $user->parish_id === $person->parish_id;
    }
}
