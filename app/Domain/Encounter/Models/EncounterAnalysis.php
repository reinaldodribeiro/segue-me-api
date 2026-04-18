<?php

namespace App\Domain\Encounter\Models;

use App\Support\Enums\AnalysisStatus;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EncounterAnalysis extends Model
{
    use HasUuid;

    protected $fillable = [
        'encounter_id',
        'general_analysis',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AnalysisStatus::class,
            'generated_at' => 'datetime',
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function teamAnalyses(): HasMany
    {
        return $this->hasMany(TeamAnalysis::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === AnalysisStatus::Completed;
    }
}
