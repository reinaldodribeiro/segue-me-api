<?php

namespace App\Domain\Encounter\Repositories;

use App\Domain\Encounter\Models\Encounter;
use Illuminate\Pagination\LengthAwarePaginator;

interface EncounterRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findOrFail(string $id): Encounter;

    public function create(array $data): Encounter;

    public function update(Encounter $encounter, array $data): Encounter;

    public function delete(Encounter $encounter): void;
}
