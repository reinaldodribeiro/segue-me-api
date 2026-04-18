<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\DTOs\CreateEncounterDTO;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Support\Enums\EncounterStatus;
use Illuminate\Support\Facades\DB;

class CreateEncounter
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
        private readonly CopyTeamTemplates $copyTeamTemplates,
    ) {}

    public function execute(CreateEncounterDTO $dto): Encounter
    {
        return DB::transaction(function () use ($dto) {
            $movement = Movement::findOrFail($dto->movementId);

            $editionNumber = $dto->editionNumber ?? $movement->nextEditionNumber();

            $encounter = $this->encounters->create([
                'parish_id' => $dto->parishId,
                'movement_id' => $dto->movementId,
                'responsible_user_id' => $dto->responsibleUserId,
                'name' => $dto->name,
                'edition_number' => $editionNumber,
                'date' => $dto->date,
                'duration_days' => $dto->durationDays,
                'location' => $dto->location,
                'status' => EncounterStatus::Draft->value,
                'max_participants' => $dto->maxParticipants,
            ]);

            $this->copyTeamTemplates->execute($encounter, $movement);

            return $encounter->load(['teams', 'movement']);
        });
    }
}
