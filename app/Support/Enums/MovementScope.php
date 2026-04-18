<?php

namespace App\Support\Enums;

enum MovementScope: string
{
    case Parish = 'parish';
    case Sector = 'sector';
    case Diocese = 'diocese';

    public function label(): string
    {
        return match ($this) {
            self::Parish => 'Paroquial',
            self::Sector => 'Setorial',
            self::Diocese => 'Diocesano',
        };
    }
}
