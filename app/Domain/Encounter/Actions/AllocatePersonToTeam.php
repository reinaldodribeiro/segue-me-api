<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Events\PersonAllocated;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Encounter\Repositories\TeamMemberRepositoryInterface;
use App\Domain\People\Models\Person;
use App\Exceptions\EncounterNotEditableException;
use App\Exceptions\IncompatiblePersonTypeException;
use App\Exceptions\PersonAlreadyAllocatedException;
use App\Exceptions\TeamFullException;
use App\Support\CacheKey;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AllocatePersonToTeam
{
    public function __construct(
        private readonly TeamMemberRepositoryInterface $members,
    ) {}

    public function execute(Team $team, Person $person, string $role = 'member'): TeamMember
    {
        $encounter = $team->encounter;

        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);
        throw_if($team->isFull(), TeamFullException::class);
        throw_if(
            ! $team->accepted_type->accepts($person->type),
            IncompatiblePersonTypeException::class
        );

        $existing = $this->members->findByPersonAndEncounter($person->id, $encounter->id);
        throw_if($existing, PersonAlreadyAllocatedException::class);

        return DB::transaction(function () use ($team, $person, $role) {
            $member = $this->members->create([
                'team_id' => $team->id,
                'person_id' => $person->id,
                'role' => $role,
                'status' => TeamMemberStatus::Pending->value,
                'invited_at' => now(),
            ]);

            Cache::forget(CacheKey::teamSuggestions($team->id));

            event(new PersonAllocated($member));

            return $member->load('person');
        });
    }
}
