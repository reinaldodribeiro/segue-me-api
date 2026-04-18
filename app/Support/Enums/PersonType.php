<?php

namespace App\Support\Enums;

enum PersonType: string
{
    case Youth = 'youth';
    case Couple = 'couple';

    public function label(): string
    {
        return match ($this) {
            self::Youth => 'Jovem',
            self::Couple => 'Casal',
        };
    }
}
