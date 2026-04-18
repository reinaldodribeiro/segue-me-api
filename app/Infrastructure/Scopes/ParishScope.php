<?php

namespace App\Infrastructure\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ParishScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $role = $user->roles->first()?->name;

        match ($role) {
            'super_admin', 'diocese_admin' => null,

            'sector_admin' => $builder->whereHas(
                'parish',
                fn (Builder $q) => $q->where('sector_id', $user->sector_id)
            ),

            default => $builder->where('parish_id', $user->parish_id),
        };
    }
}
