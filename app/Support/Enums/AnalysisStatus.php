<?php

namespace App\Support\Enums;

enum AnalysisStatus: string
{
    case Pending = 'pending';
    case Generating = 'generating';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Generating => 'Gerando',
            self::Completed => 'Concluída',
            self::Failed => 'Falhou',
        };
    }
}
