<?php

namespace App\Domain\Encounter\Repositories;

use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\MovementTeam;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MovementRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function findOrFail(string $id): Movement;

    public function create(array $data): Movement;

    public function update(Movement $movement, array $data): Movement;

    public function delete(Movement $movement): void;

    // Equipes padrão (templates)
    public function findTeamOrFail(string $movementTeamId): MovementTeam;

    public function teamsOf(string $movementId): Collection;

    public function deleteTeam(MovementTeam $movementTeam): void;

    public function activeIds(): Collection;
}
