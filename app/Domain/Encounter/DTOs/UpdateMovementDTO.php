<?php

namespace App\Domain\Encounter\DTOs;

use App\Http\Requests\Encounter\UpdateMovementRequest;
use App\Support\Enums\MovementScope;
use App\Support\Enums\TeamAcceptedType;

final readonly class UpdateMovementDTO
{
    public function __construct(
        public string $name,
        public TeamAcceptedType $targetAudience,
        public MovementScope $scope,
        public ?string $description,
        public ?bool $active,
    ) {}

    public static function fromRequest(UpdateMovementRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            targetAudience: TeamAcceptedType::from($request->validated('target_audience')),
            scope: MovementScope::from($request->validated('scope')),
            description: $request->validated('description'),
            active: $request->validated('active'),
        );
    }
}
