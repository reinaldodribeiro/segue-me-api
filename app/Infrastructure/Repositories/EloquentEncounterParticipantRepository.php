<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Encounter\Models\EncounterParticipant;
use App\Domain\Encounter\Repositories\EncounterParticipantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentEncounterParticipantRepository implements EncounterParticipantRepositoryInterface
{
    public function findByEncounter(string $encounterId): Collection
    {
        return EncounterParticipant::where('encounter_id', $encounterId)
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(string $encounterId, string $participantId): EncounterParticipant
    {
        return EncounterParticipant::where('encounter_id', $encounterId)
            ->findOrFail($participantId);
    }

    public function store(array $data): EncounterParticipant
    {
        return EncounterParticipant::create($data);
    }

    public function update(EncounterParticipant $participant, array $data): EncounterParticipant
    {
        $participant->update($data);

        return $participant->fresh();
    }

    public function updatePhoto(EncounterParticipant $participant, string $photoPath): EncounterParticipant
    {
        $participant->photo = $photoPath;
        $participant->save();

        return $participant;
    }

    public function delete(EncounterParticipant $participant): void
    {
        $participant->delete();
    }

    public function countByEncounter(string $encounterId): int
    {
        return EncounterParticipant::where('encounter_id', $encounterId)->count();
    }
}
