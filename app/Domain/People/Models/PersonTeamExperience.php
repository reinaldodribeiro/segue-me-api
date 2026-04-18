<?php

namespace App\Domain\People\Models;

use App\Domain\Encounter\Models\MovementTeam;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonTeamExperience extends Model
{
    use HasUuid;

    protected $fillable = ['person_id', 'movement_team_id', 'team_name', 'role', 'year'];

    protected function casts(): array
    {
        return ['year' => 'integer'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function movementTeam(): BelongsTo
    {
        return $this->belongsTo(MovementTeam::class);
    }
}
