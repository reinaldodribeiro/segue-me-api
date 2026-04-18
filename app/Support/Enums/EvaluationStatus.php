<?php

namespace App\Support\Enums;

enum EvaluationStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Submitted => 'Submetida',
        };
    }
}
