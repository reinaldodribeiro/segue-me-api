<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\DTOs\CreateMovementDTO;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Repositories\MovementRepositoryInterface;

class CreateMovement
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function execute(CreateMovementDTO $dto): Movement
    {
        return $this->movements->create([
            'name' => $dto->name,
            'target_audience' => $dto->targetAudience->value,
            'scope' => $dto->scope->value,
            'description' => $dto->description,
            'active' => true,
        ]);
    }
}
