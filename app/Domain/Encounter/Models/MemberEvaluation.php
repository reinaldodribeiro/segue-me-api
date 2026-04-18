<?php

namespace App\Domain\Encounter\Models;

use App\Domain\People\Models\Person;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberEvaluation extends Model
{
    use HasUuid;

    protected $fillable = [
        'team_evaluation_id',
        'team_member_id',
        'person_id',
        'commitment_rating',
        'fulfilled_responsibilities',
        'positive_highlight',
        'issue_observed',
        'recommend',
    ];

    public function teamEvaluation(): BelongsTo
    {
        return $this->belongsTo(TeamEvaluation::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
