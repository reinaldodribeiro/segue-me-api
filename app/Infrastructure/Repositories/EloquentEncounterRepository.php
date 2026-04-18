<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentEncounterRepository implements EncounterRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Encounter::with(['movement', 'responsibleUser'])
            ->orderBy('date', 'desc');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['movement_id'])) {
            $query->where('movement_id', $filters['movement_id']);
        }

        // Scope to specific movements (for coordinators)
        if (array_key_exists('movement_ids', $filters)) {
            $query->whereIn('movement_id', $filters['movement_ids']);
        }

        return $query->paginate($perPage);
    }

    public function findOrFail(string $id): Encounter
    {
        return Encounter::findOrFail($id);
    }

    public function create(array $data): Encounter
    {
        return Encounter::create($data);
    }

    public function update(Encounter $encounter, array $data): Encounter
    {
        $encounter->update($data);

        return $encounter->refresh();
    }

    public function delete(Encounter $encounter): void
    {
        $encounter->delete();
    }
}
