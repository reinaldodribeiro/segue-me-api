<?php

namespace App\Support\Enums;

enum TeamAcceptedType: string
{
    case Youth = 'youth';
    case Couple = 'couple';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Youth => 'Jovens',
            self::Couple => 'Casais',
            self::All => 'Todos',
        };
    }

    public function accepts(PersonType $personType): bool
    {
        return match ($this) {
            self::All => true,
            self::Youth => $personType === PersonType::Youth,
            self::Couple => $personType === PersonType::Couple,
        };
    }
}
