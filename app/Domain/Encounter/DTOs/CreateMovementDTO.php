<?php

namespace App\Domain\Encounter\DTOs;

use App\Http\Requests\Encounter\StoreMovementRequest;
use App\Support\Enums\MovementScope;
use App\Support\Enums\TeamAcceptedType;

final readonly class CreateMovementDTO
{
    public function __construct(
        public string $name,
        public TeamAcceptedType $targetAudience,
        public MovementScope $scope,
        public ?string $description,
    ) {}

    public static function fromRequest(StoreMovementRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            targetAudience: TeamAcceptedType::from($request->validated('target_audience')),
            scope: MovementScope::from($request->validated('scope')),
            description: $request->validated('description'),
        );
    }
}
