<?php

namespace App\Domain\People\Models;

use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Parish\Models\Parish;
use App\Support\Enums\PersonType;
use App\Support\Traits\BelongsToParish;
use App\Support\Traits\HasUuid;
use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use BelongsToParish, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'parish_id',
        'type',
        'name',
        'partner_name',
        'photo',
        'birth_date',
        'partner_birth_date',
        'wedding_date',
        'email',
        'skills',
        'notes',
        'engagement_score',
        'active',
        'encounter_year',
        // Common fields
        'nickname',
        'address',
        'birthplace',
        'phones',
        'church_movement',
        'received_at',
        'encounter_details',
        // Youth-only fields
        'father_name',
        'mother_name',
        'education_level',
        'education_status',
        'course',
        'institution',
        'sacraments',
        'available_schedule',
        'musical_instruments',
        'talks_testimony',
        // Couple-only fields
        'partner_nickname',
        'partner_birthplace',
        'partner_email',
        'partner_phones',
        'partner_photo',
        'home_phones',
    ];

    protected function casts(): array
    {
        return [
            'type' => PersonType::class,
            'birth_date' => 'date',
            'partner_birth_date' => 'date',
            'wedding_date' => 'date',
            'skills' => 'array',
            'active' => 'boolean',
            'phones' => 'array',
            'sacraments' => 'array',
            'partner_phones' => 'array',
            'home_phones' => 'array',
            'received_at' => 'date',
        ];
    }

    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teamExperiences(): HasMany
    {
        return $this->hasMany(PersonTeamExperience::class);
    }

    public function pastTeamNames(): array
    {
        return $this->teamMembers()
            ->confirmed()
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->distinct()
            ->pluck('teams.name')
            ->toArray();
    }

    public function recentRefusalsCount(): int
    {
        return $this->teamMembers()
            ->refused()
            ->latest()
            ->limit(2)
            ->count();
    }

    public function distinctTeamsCount(): int
    {
        return $this->teamMembers()
            ->confirmed()
            ->distinct('team_id')
            ->count('team_id');
    }

    public function yearsActive(): int
    {
        $first = $this->teamMembers()->min('invited_at');

        if (! $first) {
            return 0;
        }

        return (int) now()->diffInYears($first);
    }
}
