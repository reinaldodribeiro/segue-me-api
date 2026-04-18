<?php

namespace App\Domain\Encounter\Repositories;

use App\Domain\Encounter\Models\EncounterParticipant;
use Illuminate\Database\Eloquent\Collection;

interface EncounterParticipantRepositoryInterface
{
    public function findByEncounter(string $encounterId): Collection;

    public function findOrFail(string $encounterId, string $participantId): EncounterParticipant;

    public function store(array $data): EncounterParticipant;

    public function update(EncounterParticipant $participant, array $data): EncounterParticipant;

    public function updatePhoto(EncounterParticipant $participant, string $photoPath): EncounterParticipant;

    public function delete(EncounterParticipant $participant): void;

    public function countByEncounter(string $encounterId): int;
}
