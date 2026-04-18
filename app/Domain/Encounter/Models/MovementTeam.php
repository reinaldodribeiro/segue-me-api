<?php

namespace App\Domain\Encounter\Models;

use App\Support\Enums\TeamAcceptedType;
use App\Support\Traits\HasUuid;
use Database\Factories\MovementTeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovementTeam extends Model
{
    /** @use HasFactory<MovementTeamFactory> */
    use HasFactory, HasUuid;

    protected static function newFactory(): MovementTeamFactory
    {
        return MovementTeamFactory::new();
    }

    protected $fillable = [
        'movement_id',
        'name',
        'icon',
        'min_members',
        'max_members',
        'coordinators_youth',
        'coordinators_couples',
        'accepted_type',
        'recommended_skills',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'accepted_type' => TeamAcceptedType::class,
            'recommended_skills' => 'array',
        ];
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }
}
