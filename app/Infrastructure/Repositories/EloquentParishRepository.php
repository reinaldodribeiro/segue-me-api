<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentParishRepository implements ParishRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Parish::with('sector.diocese')->orderBy('name');

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (! empty($filters['sector_id'])) {
            $query->where('sector_id', $filters['sector_id']);
        } elseif (! empty($filters['diocese_id'])) {
            $query->whereHas('sector', fn ($q) => $q->where('diocese_id', $filters['diocese_id']));
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('active', (bool) $filters['active']);
        }

        return $query->paginate($perPage);
    }

    public function paginateBySector(string $sectorId, int $perPage = 20): LengthAwarePaginator
    {
        return Parish::where('sector_id', $sectorId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(string $id): Parish
    {
        return Parish::findOrFail($id);
    }

    public function create(array $data): Parish
    {
        return Parish::create($data);
    }

    public function update(Parish $parish, array $data): Parish
    {
        $parish->update($data);

        return $parish->refresh();
    }

    public function delete(Parish $parish): void
    {
        $parish->delete();
    }
}
