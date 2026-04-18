<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Encounter\Repositories\TeamMemberRepositoryInterface;

class EloquentTeamMemberRepository implements TeamMemberRepositoryInterface
{
    public function findOrFail(string $id): TeamMember
    {
        return TeamMember::findOrFail($id);
    }

    public function create(array $data): TeamMember
    {
        return TeamMember::create($data);
    }

    public function update(TeamMember $member, array $data): TeamMember
    {
        $member->update($data);

        return $member->refresh();
    }

    public function delete(TeamMember $member): void
    {
        $member->delete();
    }

    public function findByPersonAndEncounter(string $personId, string $encounterId): ?TeamMember
    {
        return TeamMember::whereHas(
            'team',
            fn ($q) => $q->where('encounter_id', $encounterId)
        )
            ->where('person_id', $personId)
            ->whereNotIn('status', ['refused'])
            ->first();
    }
}
