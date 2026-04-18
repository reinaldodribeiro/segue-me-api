<?php

namespace App\Domain\Encounter\DTOs;

use App\Http\Requests\Encounter\StoreMovementTeamRequest;
use App\Support\Enums\TeamAcceptedType;

final readonly class CreateMovementTeamDTO
{
    public function __construct(
        public string $movementId,
        public string $name,
        public ?string $icon,
        public int $minMembers,
        public int $maxMembers,
        public int $coordinatorsYouth,
        public int $coordinatorsCouples,
        public TeamAcceptedType $acceptedType,
        public array $recommendedSkills,
        public int $order,
    ) {}

    public static function fromRequest(StoreMovementTeamRequest $request, string $movementId): self
    {
        return new self(
            movementId: $movementId,
            name: $request->validated('name'),
            icon: $request->validated('icon'),
            minMembers: $request->validated('min_members'),
            maxMembers: $request->validated('max_members'),
            coordinatorsYouth: (int) $request->validated('coordinators_youth', 0),
            coordinatorsCouples: (int) $request->validated('coordinators_couples', 0),
            acceptedType: TeamAcceptedType::from($request->validated('accepted_type')),
            recommendedSkills: $request->validated('recommended_skills', []),
            order: $request->validated('order', 0),
        );
    }
}
