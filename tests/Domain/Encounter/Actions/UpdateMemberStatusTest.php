<?php

use App\Domain\Encounter\Actions\UpdateMemberStatus;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\People\Models\Person;
use App\Exceptions\EncounterNotEditableException;
use App\Support\Enums\EncounterStatus;
use App\Support\Enums\PersonType;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\TeamMemberStatus;

it('confirms a pending member', function () {
    $encounter = Encounter::factory()->create(['status' => EncounterStatus::Draft]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id, 'accepted_type' => TeamAcceptedType::All]);
    $person = Person::factory()->create(['parish_id' => $encounter->parish_id, 'type' => PersonType::Youth]);
    $member = TeamMember::factory()->create([
        'team_id' => $team->id,
        'person_id' => $person->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    $updated = app(UpdateMemberStatus::class)->execute($member, TeamMemberStatus::Confirmed);

    expect($updated->status)->toBe(TeamMemberStatus::Confirmed)
        ->and($updated->responded_at)->not->toBeNull();
});

it('marks a pending member as refused with reason', function () {
    $encounter = Encounter::factory()->create(['status' => EncounterStatus::Draft]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id, 'accepted_type' => TeamAcceptedType::All]);
    $person = Person::factory()->create(['parish_id' => $encounter->parish_id, 'type' => PersonType::Youth]);
    $member = TeamMember::factory()->create([
        'team_id' => $team->id,
        'person_id' => $person->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    $updated = app(UpdateMemberStatus::class)->execute($member, TeamMemberStatus::Refused, 'Indisponível na data.');

    expect($updated->status)->toBe(TeamMemberStatus::Refused)
        ->and($updated->refusal_reason)->toBe('Indisponível na data.');
});

it('throws when encounter is completed', function () {
    $encounter = Encounter::factory()->create(['status' => EncounterStatus::Completed]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id, 'accepted_type' => TeamAcceptedType::All]);
    $person = Person::factory()->create(['parish_id' => $encounter->parish_id, 'type' => PersonType::Youth]);
    $member = TeamMember::factory()->create([
        'team_id' => $team->id,
        'person_id' => $person->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    expect(fn () => app(UpdateMemberStatus::class)->execute($member, TeamMemberStatus::Confirmed))
        ->toThrow(EncounterNotEditableException::class);
});
