<?php

namespace App\Infrastructure\Repositories;

use App\Domain\People\Models\Person;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentPersonRepository implements PersonRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $query = Person::query()->with('parish')->where('active', true);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('nickname', 'ilike', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['skills'])) {
            foreach ((array) $filters['skills'] as $skill) {
                $query->whereJsonContains('skills', $skill);
            }
        }

        if (! empty($filters['encounter_year'])) {
            $query->where('encounter_year', (int) $filters['encounter_year']);
        }

        if (! empty($filters['parish_id'])) {
            $query->where('parish_id', $filters['parish_id']);
        } elseif (! empty($filters['sector_id'])) {
            $query->whereHas('parish', fn ($q) => $q->where('sector_id', $filters['sector_id']));
        } elseif (! empty($filters['diocese_id'])) {
            $query->whereHas('parish', fn ($q) => $q->whereHas('sector', fn ($q2) => $q2->where('diocese_id', $filters['diocese_id'])));
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowedSort = ['name', 'engagement_score'];
        $allowedDir = ['asc', 'desc'];

        if (! in_array($sortBy, $allowedSort)) {
            $sortBy = 'name';
        }
        if (! in_array($sortDir, $allowedDir)) {
            $sortDir = 'asc';
        }

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    public function findOrFail(string $id): Person
    {
        return Person::findOrFail($id);
    }

    public function findAvailableForEncounter(string $encounterId, array $filters = []): Collection
    {
        $query = Person::query()
            ->with(['teamMembers.team', 'teamExperiences'])
            ->where('active', true)
            ->whereDoesntHave('teamMembers', function ($q) use ($encounterId) {
                $q->whereHas('team', fn ($q2) => $q2->where('encounter_id', $encounterId))
                    ->whereNotIn('status', ['refused']);
            });

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('nickname', 'ilike', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['skills'])) {
            foreach ((array) $filters['skills'] as $skill) {
                $query->whereJsonContains('skills', $skill);
            }
        }

        if (! empty($filters['never_in_movement'])) {
            $query->whereDoesntHave('teamMembers.team.encounter', fn ($q) => $q->where('movement_id', $filters['never_in_movement']));
        }

        if (! empty($filters['worked_in_movement_team'])) {
            $mtId = $filters['worked_in_movement_team'];
            $query->where(function ($q) use ($mtId) {
                $q->whereHas('teamMembers.team', fn ($q2) => $q2->where('movement_team_id', $mtId))
                    ->orWhereHas('teamExperiences', fn ($q2) => $q2->where('movement_team_id', $mtId));
            });
        }

        return $query->orderBy('engagement_score', 'desc')->get();
    }

    public function create(array $data): Person
    {
        return Person::create($data);
    }

    public function update(Person $person, array $data): Person
    {
        $person->update($data);

        return $person->refresh();
    }

    public function delete(Person $person): void
    {
        $person->delete();
    }

    public function insertMany(array $rows): void
    {
        $now = now()->toDateTimeString();

        foreach (array_chunk($rows, 100) as $chunk) {
            $prepared = array_map(fn (array $row) => [
                'id' => Str::uuid()->toString(),
                'created_at' => $now,
                'updated_at' => $now,
                ...$row,
            ], $chunk);

            DB::table('people')->insertOrIgnore($prepared);
        }
    }
}
