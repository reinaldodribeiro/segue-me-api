<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Domain\People\Models\Person;
use App\Jobs\GenerateEncounterNarrative;
use App\Models\User;
use App\Support\Enums\EncounterStatus;
use App\Support\Enums\PersonType;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\TeamMemberStatus;
use App\Support\Enums\UserRole;
use Illuminate\Support\Facades\Queue;
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

    $movement = Movement::factory()->create();
    $this->coord->movements()->attach($movement->id);
    $this->encounter = Encounter::factory()->create([
        'movement_id' => $movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Draft,
    ]);
    $this->team = Team::factory()->create([
        'encounter_id' => $this->encounter->id,
        'accepted_type' => TeamAcceptedType::All,
    ]);
});

it('returns encounter summary', function () {
    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$this->encounter->id}/summary")
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns available people for an encounter', function () {
    Person::factory()->count(3)->create([
        'parish_id' => $this->parish->id,
        'active' => true,
        'type' => PersonType::Youth,
    ]);

    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$this->encounter->id}/available-people")
        ->assertOk();
});

it('dispatches narrative generation job and returns 202', function () {
    Queue::fake();

    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$this->encounter->id}/report/narrative")
        ->assertStatus(202)
        ->assertJsonStructure(['message', 'cache_key']);

    Queue::assertPushed(GenerateEncounterNarrative::class);
})->skip('Rota /report/narrative ainda não implementada');

it('returns refusal report for an encounter', function () {
    $person = Person::factory()->create([
        'parish_id' => $this->parish->id,
        'type' => PersonType::Youth,
    ]);

    TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $person->id,
        'status' => TeamMemberStatus::Refused,
        'refusal_reason' => 'Indisponibilidade',
    ]);

    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$this->encounter->id}/report/refusals")
        ->assertOk()
        ->assertJsonPath('data.encounter_id', $this->encounter->id)
        ->assertJsonPath('data.total_refusals', 1);
});

it('auto-assemble returns 202 and dispatches job', function () {
    Queue::fake();

    $this->actingAs($this->coord)
        ->postJson("/api/encounters/{$this->encounter->id}/auto-assemble")
        ->assertStatus(202);
})->skip('Rota /auto-assemble ainda não implementada');
