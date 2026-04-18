<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\Team;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Models\User;
use App\Support\Enums\EncounterStatus;
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
    $this->admin = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->admin->assignRole(UserRole::ParishAdmin->value);
    $this->coord = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->coord->assignRole(UserRole::Coordinator->value);
    $this->movement = Movement::factory()->create();
    $this->coord->movements()->attach($this->movement->id);
    $this->admin->movements()->attach($this->movement->id);
});

it('coordinator can create an encounter', function () {
    $this->actingAs($this->coord)
        ->postJson('/api/encounters', [
            'movement_id' => $this->movement->id,
            'name' => '1º Encontro 2025',
            'date' => '2025-06-15',
            'location' => 'Centro Paroquial',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', '1º Encontro 2025')
        ->assertJsonPath('data.status', EncounterStatus::Draft->value);
});

it('shows encounter with teams', function () {
    $encounter = Encounter::factory()->create([
        'movement_id' => $this->movement->id,
        'parish_id' => $this->parish->id,
    ]);

    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$encounter->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $encounter->id)
        ->assertJsonStructure(['data' => ['teams']]);
});

it('prevents editing a completed encounter', function () {
    $encounter = Encounter::factory()->create([
        'movement_id' => $this->movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Completed,
    ]);

    $this->actingAs($this->coord)
        ->putJson("/api/encounters/{$encounter->id}", [
            'name' => 'Novo Nome',
        ])
        ->assertStatus(422);
});

it('prevents editing metadata of confirmed encounter', function () {
    $encounter = Encounter::factory()->create([
        'movement_id' => $this->movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Confirmed,
    ]);

    $this->actingAs($this->coord)
        ->patchJson("/api/encounters/{$encounter->id}", [
            'name' => 'Novo Nome',
        ])
        ->assertStatus(422);
});

it('allows changing status of confirmed encounter to completed', function () {
    Queue::fake();

    $encounter = Encounter::factory()->create([
        'movement_id' => $this->movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Confirmed,
    ]);
    Team::factory()->create(['encounter_id' => $encounter->id, 'min_members' => 0]);

    $this->actingAs($this->coord)
        ->patchJson("/api/encounters/{$encounter->id}", [
            'status' => EncounterStatus::Completed->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.status', EncounterStatus::Completed->value);
});

it('only parish admin can delete an encounter', function () {
    $encounter = Encounter::factory()->create([
        'movement_id' => $this->movement->id,
        'parish_id' => $this->parish->id,
    ]);

    $this->actingAs($this->coord)
        ->deleteJson("/api/encounters/{$encounter->id}")
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->deleteJson("/api/encounters/{$encounter->id}")
        ->assertOk();
});

it('cannot access encounter from another parish', function () {
    $otherDiocese = Diocese::factory()->create();
    $otherSector = Sector::factory()->create(['diocese_id' => $otherDiocese->id]);
    $otherParish = Parish::factory()->create(['sector_id' => $otherSector->id]);
    $otherMovement = Movement::factory()->create();
    $encounter = Encounter::factory()->create([
        'movement_id' => $otherMovement->id,
        'parish_id' => $otherParish->id,
    ]);

    $this->actingAs($this->coord)
        ->getJson("/api/encounters/{$encounter->id}")
        ->assertNotFound();
});
