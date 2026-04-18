<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Repositories\TeamRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentTeamRepository implements TeamRepositoryInterface
{
    public function findOrFail(string $id): Team
    {
        return Team::findOrFail($id);
    }

    public function findByEncounter(string $encounterId): Collection
    {
        return Team::where('encounter_id', $encounterId)
            ->with(['members.person'])
            ->orderBy('order')
            ->get();
    }

    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);

        return $team->refresh();
    }

    public function delete(Team $team): void
    {
        $team->delete();
    }
}
