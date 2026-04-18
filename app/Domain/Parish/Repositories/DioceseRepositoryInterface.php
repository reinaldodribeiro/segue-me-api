<?php

namespace App\Domain\Parish\Repositories;

use App\Domain\Parish\Models\Diocese;
use Illuminate\Pagination\LengthAwarePaginator;

interface DioceseRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(string $id): Diocese;

    public function findBySlug(string $slug): ?Diocese;

    public function create(array $data): Diocese;

    public function update(Diocese $diocese, array $data): Diocese;

    public function delete(Diocese $diocese): void;
}
