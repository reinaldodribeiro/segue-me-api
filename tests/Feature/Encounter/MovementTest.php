<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Models\User;
use App\Support\Enums\MovementScope;
use App\Support\Enums\PersonType;
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
});

it('coordinator can list movements', function () {
    $movements = Movement::factory()->count(2)->create();
    $this->coord->movements()->attach($movements->pluck('id'));

    $this->actingAs($this->coord)
        ->getJson('/api/movements')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('parish admin can create a movement', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/movements', [
            'name' => 'Encontro de Jovens',
            'target_audience' => PersonType::Youth->value,
            'scope' => MovementScope::Parish->value,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Encontro de Jovens');
});

it('coordinator cannot create a movement', function () {
    $this->actingAs($this->coord)
        ->postJson('/api/movements', [
            'name' => 'Encontro de Jovens',
            'target_audience' => PersonType::Youth->value,
            'scope' => MovementScope::Parish->value,
        ])
        ->assertForbidden();
});

it('parish admin can update a movement', function () {
    $movement = Movement::factory()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/movements/{$movement->id}", [
            'name' => 'Nome Atualizado',
            'target_audience' => $movement->target_audience->value,
            'scope' => $movement->scope->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nome Atualizado');
});

it('prevents deletion of movement with encounters', function () {
    $movement = Movement::factory()->create();
    Encounter::factory()->create([
        'movement_id' => $movement->id,
        'parish_id' => $this->parish->id,
    ]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/movements/{$movement->id}")
        ->assertStatus(422);
});
