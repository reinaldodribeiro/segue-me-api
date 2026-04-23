<?php

namespace App\Infrastructure\Repositories;

use App\Domain\People\Models\Person;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentPersonRepository implements PersonRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $query = Person::query()->where('active', true);

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

    public function findAvailableForEncounter(string $encounterId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildAvailableForEncounterQuery($encounterId, $filters);

        $paginator = $query
            ->orderBy('engagement_score', 'desc')
            ->paginate($perPage);

        // Eager-load relations needed by PersonAvailabilityResource in a single batch query
        // Only select the columns the resource actually reads to avoid hydrating full Team objects
        $paginator->getCollection()->load(['teamMembers.team:id,name,movement_team_id', 'teamExperiences:id,person_id,movement_team_id,team_name']);

        return $paginator;
    }

    public function findAllAvailableForEncounter(string $encounterId, array $filters = []): Collection
    {
        $people = $this->buildAvailableForEncounterQuery($encounterId, $filters)
            ->orderBy('engagement_score', 'desc')
            ->limit(500)
            ->get();

        // Eager-load relations needed by PersonAvailabilityResource in a single batch query
        // Only select the columns the resource actually reads to avoid hydrating full Team objects
        $people->load(['teamMembers.team:id,name,movement_team_id', 'teamExperiences:id,person_id,movement_team_id,team_name']);

        return $people;
    }

    private function buildAvailableForEncounterQuery(string $encounterId, array $filters = []): Builder
    {
        $query = Person::query()
            ->select([
                'id', 'parish_id', 'type', 'name', 'partner_name', 'photo',
                'phones', 'email', 'skills', 'engagement_score', 'active',
                'encounter_year', 'nickname',
            ])
            ->where('active', true)
            ->whereNotExists(function ($sub) use ($encounterId) {
                $sub->selectRaw('1')
                    ->from('team_members')
                    ->join('teams', 'teams.id', '=', 'team_members.team_id')
                    ->whereColumn('team_members.person_id', 'people.id')
                    ->where('teams.encounter_id', $encounterId)
                    ->whereNotIn('team_members.status', ['refused']);
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
            $query->whereNotExists(function ($sub) use ($filters) {
                $sub->selectRaw('1')
                    ->from('team_members')
                    ->join('teams', 'teams.id', '=', 'team_members.team_id')
                    ->join('encounters', 'encounters.id', '=', 'teams.encounter_id')
                    ->whereColumn('team_members.person_id', 'people.id')
                    ->where('encounters.movement_id', $filters['never_in_movement']);
            });
        }

        if (array_key_exists('priority_previous_ids', $filters)) {
            $ids = $filters['priority_previous_ids'];
            // Only people who participated as encontristas in the previous encounter
            $query->whereIn('id', ! empty($ids) ? $ids : ['__none__']);
        }

        if (! empty($filters['worked_in_movement_team'])) {
            $mtId = $filters['worked_in_movement_team'];
            $query->where(function ($q) use ($mtId) {
                $q->whereExists(function ($sub) use ($mtId) {
                    $sub->selectRaw('1')
                        ->from('team_members')
                        ->join('teams', 'teams.id', '=', 'team_members.team_id')
                        ->whereColumn('team_members.person_id', 'people.id')
                        ->where('teams.movement_team_id', $mtId);
                })->orWhereHas('teamExperiences', fn ($q2) => $q2->where('movement_team_id', $mtId));
            });
        }

        return $query;
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
