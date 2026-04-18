<?php

namespace App\Domain\Parish\Repositories;

use App\Domain\Parish\Models\Parish;
use Illuminate\Pagination\LengthAwarePaginator;

interface ParishRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function paginateBySector(string $sectorId, int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(string $id): Parish;

    public function create(array $data): Parish;

    public function update(Parish $parish, array $data): Parish;

    public function delete(Parish $parish): void;
}
