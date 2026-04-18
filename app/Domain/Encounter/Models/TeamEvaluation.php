<?php

namespace App\Domain\Encounter\Models;

use App\Support\Enums\EvaluationStatus;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamEvaluation extends Model
{
    use HasUuid;

    protected $fillable = [
        'team_id',
        'encounter_id',
        'token',
        'pin',
        'status',
        'expires_at',
        'preparation_rating',
        'preparation_comment',
        'teamwork_rating',
        'teamwork_comment',
        'materials_rating',
        'materials_comment',
        'issues_text',
        'improvements_text',
        'overall_team_rating',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EvaluationStatus::class,
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function memberEvaluations(): HasMany
    {
        return $this->hasMany(MemberEvaluation::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === EvaluationStatus::Submitted;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', EvaluationStatus::Submitted);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', EvaluationStatus::Pending);
    }
}
