<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;

class CopyTeamTemplates
{
    public function execute(Encounter $encounter, Movement $movement): void
    {
        $movement->loadMissing('movementTeams');

        foreach ($movement->movementTeams as $template) {
            $encounter->teams()->create([
                'movement_team_id' => $template->id,
                'name' => $template->name,
                'min_members' => $template->min_members,
                'max_members' => $template->max_members,
                'coordinators_youth' => $template->coordinators_youth,
                'coordinators_couples' => $template->coordinators_couples,
                'accepted_type' => $template->accepted_type->value,
                'recommended_skills' => $template->recommended_skills,
                'order' => $template->order,
            ]);
        }
    }
}
