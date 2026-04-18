<?php

namespace App\Support\Enums;

enum TeamMemberRole: string
{
    case Coordinator = 'coordinator';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Coordinator => 'Coordenador',
            self::Member => 'Integrante',
        };
    }
}
