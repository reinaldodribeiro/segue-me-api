<?php

namespace App\Support\Traits;

use App\Infrastructure\Scopes\ParishScope;

trait BelongsToParish
{
    protected static function bootBelongsToParish(): void
    {
        static::addGlobalScope(new ParishScope);
    }
}
