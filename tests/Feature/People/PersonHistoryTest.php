<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Domain\People\Models\Person;
use App\Models\User;
use App\Support\Enums\EncounterStatus;
use App\Support\Enums\PersonType;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\TeamMemberStatus;
use App\Support\Enums\UserRole;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (UserRole::cases() as $role) {
        Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }

    $diocese = Diocese::factory()->create();
    $sector = Sector::factory()->create(['diocese_id' => $diocese->id]);
    $this->parish = Parish::factory()->create(['sector_id' => $sector->id]);
    $this->admin = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->admin->assignRole(UserRole::ParishAdmin->value);
    $this->coord = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->coord->assignRole(UserRole::Coordinator->value);

    $this->person = Person::factory()->create([
        'parish_id' => $this->parish->id,
        'type' => PersonType::Youth,
    ]);

    $movement = Movement::factory()->create();
    $this->encounter = Encounter::factory()->create([
        'movement_id' => $movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Completed,
    ]);
    $this->team = Team::factory()->create([
        'encounter_id' => $this->encounter->id,
        'accepted_type' => TeamAcceptedType::All,
    ]);
});

it('returns participation history for a person', function () {
    TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $this->person->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    $this->actingAs($this->coord)
        ->getJson("/api/people/{$this->person->id}/history")
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns empty history when person has no participations', function () {
    $this->actingAs($this->coord)
        ->getJson("/api/people/{$this->person->id}/history")
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns suggested teams for a person in an encounter', function () {
    $this->actingAs($this->coord)
        ->getJson("/api/people/{$this->person->id}/suggested-teams?encounter_id={$this->encounter->id}")
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns 422 when encounter_id missing for suggested-teams', function () {
    $this->actingAs($this->coord)
        ->getJson("/api/people/{$this->person->id}/suggested-teams")
        ->assertUnprocessable();
});

it('downloads import template', function () {
    $this->actingAs($this->admin)
        ->get('/api/people/import/template')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('downloads export excel', function () {
    Person::factory()->count(3)->create(['parish_id' => $this->parish->id]);

    $this->actingAs($this->admin)
        ->get('/api/people/export/excel')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
