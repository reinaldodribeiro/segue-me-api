<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\DTOs\CreateMovementTeamDTO;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\MovementTeam;

class CreateMovementTeam
{
    public function execute(Movement $movement, CreateMovementTeamDTO $dto): MovementTeam
    {
        return $movement->movementTeams()->create([
            'name' => $dto->name,
            'icon' => $dto->icon,
            'min_members' => $dto->minMembers,
            'max_members' => $dto->maxMembers,
            'coordinators_youth' => $dto->coordinatorsYouth,
            'coordinators_couples' => $dto->coordinatorsCouples,
            'accepted_type' => $dto->acceptedType->value,
            'recommended_skills' => $dto->recommendedSkills,
            'order' => $dto->order,
        ]);
    }
}
