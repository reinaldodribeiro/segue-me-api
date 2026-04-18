<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\DTOs\UpdateMovementDTO;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Repositories\MovementRepositoryInterface;

class UpdateMovement
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function execute(Movement $movement, UpdateMovementDTO $dto): Movement
    {
        $data = [
            'name' => $dto->name,
            'target_audience' => $dto->targetAudience->value,
            'scope' => $dto->scope->value,
            'description' => $dto->description,
        ];

        if ($dto->active !== null) {
            $data['active'] = $dto->active;
        }

        return $this->movements->update($movement, $data);
    }
}
