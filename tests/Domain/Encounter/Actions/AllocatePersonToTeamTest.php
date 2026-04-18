<?php

use App\Domain\Encounter\Actions\AllocatePersonToTeam;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\People\Models\Person;
use App\Exceptions\PersonAlreadyAllocatedException;
use App\Exceptions\TeamFullException;
use App\Support\Enums\EncounterStatus;
use App\Support\Enums\PersonType;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\TeamMemberStatus;

it('allocates a person to a team successfully', function () {
    $encounter = Encounter::factory()->create(['status' => EncounterStatus::Draft]);
    $team = Team::factory()->create([
        'encounter_id' => $encounter->id,
        'max_members' => 5,
        'accepted_type' => TeamAcceptedType::All,
    ]);
    $person = Person::factory()->create([
        'parish_id' => $encounter->parish_id,
        'type' => PersonType::Youth,
    ]);

    $member = app(AllocatePersonToTeam::class)->execute($team, $person);

    expect($member)->toBeInstanceOf(TeamMember::class)
        ->and($member->status)->toBe(TeamMemberStatus::Pending)
        ->and($member->person_id)->toBe($person->id);
});

it('throws when team is full', function () {
    $encounter = Encounter::factory()->create(['status' => EncounterStatus::Draft]);
    $team = Team::factory()->create([
        'encounter_id' => $encounter->id,
        'max_members' => 1,
        'accepted_type' => TeamAcceptedType::All,
    ]);
    $existing = Person::factory()->create(['parish_id' => $encounter->parish_id, 'type' => PersonType::Youth]);
    $newPerson = Person::factory()->create(['parish_id' => $encounter->parish_id, 'type' => PersonType::Youth]);

    TeamMember::factory()->create([
        'team_id' => $team->id,
        'person_id' => $existing->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    expect(fn () => app(AllocatePersonToTeam::class)->execute($team, $newPerson))
        ->toThrow(TeamFullException::class);
});

it('throws when person already allocated in encounter', function () {
    $encounter = Encounter::factory()->create(['status' => EncounterStatus::Draft]);
    $team1 = Team::factory()->create(['encounter_id' => $encounter->id, 'max_members' => 5, 'accepted_type' => TeamAcceptedType::All]);
    $team2 = Team::factory()->create(['encounter_id' => $encounter->id, 'max_members' => 5, 'accepted_type' => TeamAcceptedType::All]);
    $person = Person::factory()->create(['parish_id' => $encounter->parish_id, 'type' => PersonType::Youth]);

    TeamMember::factory()->create([
        'team_id' => $team1->id,
        'person_id' => $person->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    expect(fn () => app(AllocatePersonToTeam::class)->execute($team2, $person))
        ->toThrow(PersonAlreadyAllocatedException::class);
});
