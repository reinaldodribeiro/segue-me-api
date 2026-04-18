<?php

namespace App\Domain\Parish\Repositories;

use App\Domain\Parish\Models\Sector;
use Illuminate\Pagination\LengthAwarePaginator;

interface SectorRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function paginateByDiocese(string $dioceseId, int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(string $id): Sector;

    public function create(array $data): Sector;

    public function update(Sector $sector, array $data): Sector;

    public function delete(Sector $sector): void;
}
