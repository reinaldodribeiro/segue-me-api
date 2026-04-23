<?php

namespace App\Domain\Encounter\Models;

use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\TeamMemberStatus;
use App\Support\Traits\HasUuid;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory, HasUuid;

    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }

    protected $fillable = [
        'encounter_id',
        'movement_team_id',
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

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function movementTeam(): BelongsTo
    {
        return $this->belongsTo(MovementTeam::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(TeamEvaluation::class);
    }

    public function confirmedMembers(): HasMany
    {
        return $this->members()->confirmed();
    }

    public function isFull(): bool
    {
        return $this->activeCount() >= $this->max_members;
    }

    public function isBelowMinimum(): bool
    {
        return $this->confirmedCount() < $this->min_members;
    }

    /**
     * Count of non-refused members using in-memory collection when available,
     * falling back to a database COUNT only when the relation is not loaded.
     */
    public function activeCount(): int
    {
        if ($this->relationLoaded('members')) {
            return $this->members
                ->filter(fn ($m) => $m->status !== TeamMemberStatus::Refused)
                ->count();
        }

        return $this->members()->whereNotIn('status', [TeamMemberStatus::Refused->value])->count();
    }

    /**
     * Count of confirmed members using in-memory collection when available,
     * falling back to a database COUNT only when the relation is not loaded.
     */
    public function confirmedCount(): int
    {
        if ($this->relationLoaded('members')) {
            return $this->members
                ->filter(fn ($m) => $m->status === TeamMemberStatus::Confirmed)
                ->count();
        }

        return $this->members()->confirmed()->count();
    }
}
