<?php

namespace App\Domain\Encounter\Models;

use App\Domain\Parish\Models\Parish;
use App\Models\User;
use App\Support\Enums\EncounterStatus;
use App\Support\Traits\BelongsToParish;
use App\Support\Traits\HasUuid;
use Database\Factories\EncounterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encounter extends Model
{
    use BelongsToParish, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'parish_id',
        'movement_id',
        'responsible_user_id',
        'name',
        'edition_number',
        'date',
        'duration_days',
        'location',
        'status',
        'max_participants',
    ];

    protected function casts(): array
    {
        return [
            'status' => EncounterStatus::class,
            'date' => 'date',
            'duration_days' => 'integer',
            'max_participants' => 'integer',
        ];
    }

    protected static function newFactory(): EncounterFactory
    {
        return EncounterFactory::new();
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class)->orderBy('order');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TeamEvaluation::class);
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(EncounterAnalysis::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EncounterParticipant::class)->orderBy('name');
    }

    public function teamMembers(): HasManyThrough
    {
        return $this->hasManyThrough(TeamMember::class, Team::class);
    }

    public function isDraft(): bool
    {
        return $this->status === EncounterStatus::Draft;
    }

    public function isConfirmed(): bool
    {
        return $this->status === EncounterStatus::Confirmed;
    }

    public function isCompleted(): bool
    {
        return $this->status === EncounterStatus::Completed;
    }
}
