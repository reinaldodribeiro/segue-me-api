<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Repositories\DioceseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentDioceseRepository implements DioceseRepositoryInterface
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Diocese::orderBy('name')->paginate($perPage);
    }

    public function findOrFail(string $id): Diocese
    {
        return Diocese::findOrFail($id);
    }

    public function findBySlug(string $slug): ?Diocese
    {
        return Diocese::where('slug', $slug)->first();
    }

    public function create(array $data): Diocese
    {
        return Diocese::create($data);
    }

    public function update(Diocese $diocese, array $data): Diocese
    {
        $diocese->update($data);

        return $diocese->refresh();
    }

    public function delete(Diocese $diocese): void
    {
        $diocese->delete();
    }
}
