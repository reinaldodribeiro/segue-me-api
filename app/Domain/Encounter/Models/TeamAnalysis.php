<?php

namespace App\Domain\Encounter\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamAnalysis extends Model
{
    use HasUuid;

    protected $fillable = [
        'encounter_analysis_id',
        'team_id',
        'analysis',
    ];

    public function encounterAnalysis(): BelongsTo
    {
        return $this->belongsTo(EncounterAnalysis::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
