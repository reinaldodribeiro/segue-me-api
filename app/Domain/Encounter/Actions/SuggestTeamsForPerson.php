<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\People\Models\Person;
use Illuminate\Support\Collection;

class SuggestTeamsForPerson
{
    public function execute(Person $person, Encounter $encounter): Collection
    {
        $encounter->loadMissing(['teams.members']);

        return $encounter->teams
            ->filter(fn ($team) => ! $team->isFull())
            ->filter(fn ($team) => $team->accepted_type->accepts($person->type))
            ->map(function ($team) use ($person) {
                $personSkills = $person->skills ?? [];
                $recommended = $team->recommended_skills ?? [];
                $matchingSkills = array_intersect($personSkills, $recommended);
                $skillScore = count($recommended) > 0
                    ? round(count($matchingSkills) / count($recommended) * 100)
                    : 0;

                return [
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'accepted_type' => $team->accepted_type->value,
                    'slots_available' => $team->max_members - $team->members->whereNotIn('status', ['refused'])->count(),
                    'recommended_skills' => $recommended,
                    'matching_skills' => array_values($matchingSkills),
                    'skill_match_pct' => $skillScore,
                    'compatibility' => match (true) {
                        $skillScore >= 60 => 'alta',
                        $skillScore >= 30 => 'media',
                        default => 'baixa',
                    },
                ];
            })
            ->sortByDesc('skill_match_pct')
            ->values();
    }
}
