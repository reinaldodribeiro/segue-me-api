<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Parish\Models\Sector;
use App\Domain\Parish\Repositories\SectorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentSectorRepository implements SectorRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Sector::with('diocese')->orderBy('name');

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (! empty($filters['diocese_id'])) {
            $query->where('diocese_id', $filters['diocese_id']);
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('active', (bool) $filters['active']);
        }

        return $query->paginate($perPage);
    }

    public function paginateByDiocese(string $dioceseId, int $perPage = 20): LengthAwarePaginator
    {
        return Sector::where('diocese_id', $dioceseId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(string $id): Sector
    {
        return Sector::findOrFail($id);
    }

    public function create(array $data): Sector
    {
        return Sector::create($data);
    }

    public function update(Sector $sector, array $data): Sector
    {
        $sector->update($data);

        return $sector->refresh();
    }

    public function delete(Sector $sector): void
    {
        $sector->delete();
    }
}
