<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\EncounterParticipant;
use App\Domain\Encounter\Repositories\EncounterParticipantRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StoreEncounterParticipant
{
    public function __construct(
        private readonly EncounterParticipantRepositoryInterface $repo,
    ) {}

    public function execute(
        Encounter $encounter,
        array $data,
    ): EncounterParticipant {
        return DB::transaction(function () use ($encounter, $data) {
            return $this->repo->store(
                array_merge($data, ['encounter_id' => $encounter->id])
            );
        });
    }
}
