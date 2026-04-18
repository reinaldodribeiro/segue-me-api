<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\DTOs\CreateMovementTeamDTO;
use App\Domain\Encounter\Models\MovementTeam;

class UpdateMovementTeam
{
    public function execute(MovementTeam $movementTeam, CreateMovementTeamDTO $dto): MovementTeam
    {
        $movementTeam->update([
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

        return $movementTeam->refresh();
    }
}
