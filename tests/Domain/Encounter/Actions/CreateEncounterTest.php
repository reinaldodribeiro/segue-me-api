<?php

use App\Domain\Encounter\Actions\CreateEncounter;
use App\Domain\Encounter\DTOs\CreateEncounterDTO;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\MovementTeam;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Models\User;
use App\Support\Enums\EncounterStatus;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\UserRole;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (UserRole::cases() as $role) {
        Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }

    $diocese = Diocese::factory()->create();
    $sector = Sector::factory()->create(['diocese_id' => $diocese->id]);
    $this->parish = Parish::factory()->create(['sector_id' => $sector->id]);
    $this->coord = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->coord->assignRole(UserRole::Coordinator->value);
    $this->movement = Movement::factory()->create();
});

it('creates an encounter with draft status', function () {
    $dto = new CreateEncounterDTO(
        parishId: $this->parish->id,
        movementId: $this->movement->id,
        responsibleUserId: $this->coord->id,
        name: '1º Encontro',
        editionNumber: null,
        date: '2025-06-15',
        durationDays: 1,
        location: 'Centro Paroquial',
        maxParticipants: null,
    );

    $encounter = app(CreateEncounter::class)->execute($dto);

    expect($encounter)->toBeInstanceOf(Encounter::class)
        ->and($encounter->status)->toBe(EncounterStatus::Draft)
        ->and($encounter->name)->toBe('1º Encontro')
        ->and($encounter->parish_id)->toBe($this->parish->id);
});

it('auto-assigns sequential edition number', function () {
    Encounter::factory()->create([
        'movement_id' => $this->movement->id,
        'parish_id' => $this->parish->id,
        'edition_number' => 1,
    ]);

    $dto = new CreateEncounterDTO(
        parishId: $this->parish->id,
        movementId: $this->movement->id,
        responsibleUserId: $this->coord->id,
        name: '2º Encontro',
        editionNumber: null,
        date: '2025-08-01',
        durationDays: 1,
        location: null,
        maxParticipants: null,
    );

    $encounter = app(CreateEncounter::class)->execute($dto);

    expect($encounter->edition_number)->toBe(2);
});

it('copies movement team templates to the new encounter', function () {
    MovementTeam::factory()->count(3)->create([
        'movement_id' => $this->movement->id,
        'accepted_type' => TeamAcceptedType::All,
    ]);

    $dto = new CreateEncounterDTO(
        parishId: $this->parish->id,
        movementId: $this->movement->id,
        responsibleUserId: $this->coord->id,
        name: '1º Encontro',
        editionNumber: null,
        date: '2025-06-15',
        durationDays: 1,
        location: null,
        maxParticipants: null,
    );

    $encounter = app(CreateEncounter::class)->execute($dto);

    expect($encounter->teams)->toHaveCount(3);
});

it('creates encounter without templates when movement has none', function () {
    $dto = new CreateEncounterDTO(
        parishId: $this->parish->id,
        movementId: $this->movement->id,
        responsibleUserId: $this->coord->id,
        name: '1º Encontro',
        editionNumber: null,
        date: '2025-06-15',
        durationDays: 1,
        location: null,
        maxParticipants: null,
    );

    $encounter = app(CreateEncounter::class)->execute($dto);

    expect($encounter->teams)->toBeEmpty();
});
