<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\MovementTeam;
use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentMovementRepository implements MovementRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Movement::orderBy('name');

        // Filter by user's allowed movements (for coordinators)
        if (array_key_exists('movement_ids', $filters)) {
            $query->whereIn('id', $filters['movement_ids']);
        }

        return $query->paginate($perPage);
    }

    public function findOrFail(string $id): Movement
    {
        return Movement::findOrFail($id);
    }

    public function create(array $data): Movement
    {
        return Movement::create($data);
    }

    public function update(Movement $movement, array $data): Movement
    {
        $movement->update($data);

        return $movement->refresh();
    }

    public function delete(Movement $movement): void
    {
        $movement->delete();
    }

    public function findTeamOrFail(string $movementTeamId): MovementTeam
    {
        return MovementTeam::findOrFail($movementTeamId);
    }

    public function teamsOf(string $movementId): Collection
    {
        return MovementTeam::where('movement_id', $movementId)
            ->orderBy('order')
            ->get();
    }

    public function deleteTeam(MovementTeam $movementTeam): void
    {
        $movementTeam->delete();
    }

    public function activeIds(): Collection
    {
        return Movement::where('active', true)->pluck('id');
    }
}
