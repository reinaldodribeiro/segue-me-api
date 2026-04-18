<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Parish\Models\Parish;
use App\Domain\People\Actions\CreatePerson;
use App\Domain\People\DTOs\CreatePersonDTO;
use App\Domain\People\Models\Person;
use App\Support\Enums\PersonType;
use App\Support\Enums\TeamMemberStatus;

it('creates a person successfully', function () {
    $parish = Parish::factory()->create();

    $dto = new CreatePersonDTO(
        parishId: $parish->id,
        type: PersonType::Youth,
        name: 'João da Silva',
        partnerName: null,
        photo: null,
        birthDate: '2000-01-01',
        partnerBirthDate: null,
        weddingDate: null,
        phones: ['11999999999'],
        email: 'joao@example.com',
        skills: ['Música', 'Teatro'],
        notes: null,
    );

    $person = app(CreatePerson::class)->execute($dto);

    expect($person)->toBeInstanceOf(Person::class)
        ->and($person->name)->toBe('João da Silva')
        ->and($person->type)->toBe(PersonType::Youth)
        ->and($person->skills)->toContain('Música')
        ->and($person->active)->toBeTrue()
        ->and($person->engagement_score)->toBe(0);
});

it('calculates engagement score correctly', function () {
    $parish = Parish::factory()->create();
    $person = Person::factory()->create([
        'parish_id' => $parish->id,
        'engagement_score' => 0,
    ]);

    // Simula participações confirmadas
    $encounter = Encounter::factory()->create(['parish_id' => $parish->id]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id]);

    TeamMember::factory()->create([
        'person_id' => $person->id,
        'team_id' => $team->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    $confirmed = $person->teamMembers()->confirmed()->count();
    expect($confirmed)->toBe(1);
});
