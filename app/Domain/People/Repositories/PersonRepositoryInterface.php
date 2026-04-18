<?php

namespace App\Domain\People\Repositories;

use App\Domain\People\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PersonRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 30): LengthAwarePaginator;

    public function findOrFail(string $id): Person;

    public function findAvailableForEncounter(string $encounterId, array $filters = []): Collection;

    public function create(array $data): Person;

    public function update(Person $person, array $data): Person;

    public function delete(Person $person): void;

    public function insertMany(array $rows): void;
}
