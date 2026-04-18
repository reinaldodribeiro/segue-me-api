<?php

namespace App\Domain\Encounter\Repositories;

use App\Domain\Encounter\Models\Team;
use Illuminate\Support\Collection;

interface TeamRepositoryInterface
{
    public function findOrFail(string $id): Team;

    public function findByEncounter(string $encounterId): Collection;

    public function create(array $data): Team;

    public function update(Team $team, array $data): Team;

    public function delete(Team $team): void;
}
