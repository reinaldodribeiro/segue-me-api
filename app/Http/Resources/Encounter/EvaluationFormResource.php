<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $team = $this->team;
        $encounter = $this->encounter;

        $confirmedMembers = $team->members()
            ->confirmed()
            ->with('person:id,name')
            ->get();

        return [
            'encounter_name' => $encounter->name,
            'encounter_date' => $encounter->date?->format('d/m/Y'),
            'movement_name' => $encounter->movement?->name,
            'team_name' => $team->name,
            'team_icon' => $team->icon,
            'already_submitted' => $this->isSubmitted(),
            'members' => $confirmedMembers->map(fn ($m) => [
                'team_member_id' => $m->id,
                'person_name' => $m->person->name,
            ]),
        ];
    }
}
