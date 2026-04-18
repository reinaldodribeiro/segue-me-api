<?php

namespace App\Support\Enums;

enum TeamMemberStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Refused = 'refused';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Confirmed => 'Confirmado',
            self::Refused => 'Recusou',
        };
    }
}
