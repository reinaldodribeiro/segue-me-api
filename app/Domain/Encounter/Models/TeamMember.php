<?php

namespace App\Domain\Encounter\Models;

use App\Domain\People\Models\Person;
use App\Support\Enums\TeamMemberRole;
use App\Support\Enums\TeamMemberStatus;
use App\Support\Traits\HasUuid;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory, HasUuid;

    protected static function newFactory(): TeamMemberFactory
    {
        return TeamMemberFactory::new();
    }

    protected $fillable = [
        'team_id',
        'person_id',
        'replaced_by_id',
        'role',
        'status',
        'refusal_reason',
        'invited_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => TeamMemberRole::class,
            'status' => TeamMemberStatus::class,
            'invited_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function memberEvaluation(): HasOne
    {
        return $this->hasOne(MemberEvaluation::class);
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', TeamMemberStatus::Confirmed);
    }

    public function scopeRefused(Builder $query): Builder
    {
        return $query->where('status', TeamMemberStatus::Refused);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TeamMemberStatus::Pending);
    }
}
