<?php

namespace App\Support\Enums;

enum EncounterStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Confirmed => 'Confirmado',
            self::Completed => 'Realizado',
        };
    }
}
