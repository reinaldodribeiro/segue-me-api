<?php

namespace App\Support\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case DioceseAdmin = 'diocese_admin';
    case SectorAdmin = 'sector_admin';
    case ParishAdmin = 'parish_admin';
    case Coordinator = 'coordinator';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::DioceseAdmin => 'Admin de Diocese',
            self::SectorAdmin => 'Admin de Setor',
            self::ParishAdmin => 'Admin de Paróquia',
            self::Coordinator => 'Coordenador',
        };
    }

    public function canSeeAllParishes(): bool
    {
        return in_array($this, [self::SuperAdmin, self::DioceseAdmin]);
    }
}
