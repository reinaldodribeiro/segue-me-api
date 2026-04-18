<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\Team;
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

    $movement = Movement::factory()->create();
    $this->coord->movements()->attach($movement->id);
    $this->encounter = Encounter::factory()->create([
        'movement_id' => $movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Draft,
    ]);
});

it('lists teams of an encounter', function () {
    Team::factory()->count(2)->create(['encounter_id' => $this->encounter->id]);

    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$this->encounter->id}/teams")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('coordinator can add a team to an encounter', function () {
    $this->actingAs($this->coord)
        ->postJson("/api/encounters/{$this->encounter->id}/teams", [
            'name' => 'Equipe de Louvor',
            'min_members' => 2,
            'max_members' => 5,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Equipe de Louvor');
});

it('cannot add team to completed encounter', function () {
    $this->encounter->update(['status' => EncounterStatus::Completed]);

    $this->actingAs($this->coord)
        ->postJson("/api/encounters/{$this->encounter->id}/teams", [
            'name' => 'Equipe',
            'min_members' => 1,
            'max_members' => 3,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertStatus(422);
});

it('coordinator can update a team', function () {
    $team = Team::factory()->create([
        'encounter_id' => $this->encounter->id,
        'name' => 'Equipe Antiga',
        'min_members' => 1,
        'max_members' => 3,
        'accepted_type' => TeamAcceptedType::All,
    ]);

    $this->actingAs($this->coord)
        ->putJson("/api/teams/{$team->id}", [
            'name' => 'Equipe Atualizada',
            'min_members' => 1,
            'max_members' => 4,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Equipe Atualizada');
});

it('coordinator can delete a team from draft encounter', function () {
    $team = Team::factory()->create(['encounter_id' => $this->encounter->id]);

    $this->actingAs($this->coord)
        ->deleteJson("/api/teams/{$team->id}")
        ->assertOk();

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

it('cannot delete team from completed encounter', function () {
    $team = Team::factory()->create(['encounter_id' => $this->encounter->id]);
    $this->encounter->update(['status' => EncounterStatus::Completed]);

    $this->actingAs($this->coord)
        ->deleteJson("/api/teams/{$team->id}")
        ->assertStatus(422);
});
