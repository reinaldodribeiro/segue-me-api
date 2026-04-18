<?php

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Models\User;
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

it('parish admin can list users of own parish', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

it('coordinator cannot list users', function () {
    $this->actingAs($this->coord)
        ->getJson('/api/users')
        ->assertForbidden();
});

it('parish admin can create a coordinator', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/users', [
            'name' => 'Novo Coordenador',
            'email' => 'novo@test.com',
            'password' => 'password123',
            'role' => UserRole::Coordinator->value,
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'novo@test.com');
});

it('coordinator cannot create users', function () {
    $this->actingAs($this->coord)
        ->postJson('/api/users', [
            'name' => 'Teste',
            'email' => 'teste@test.com',
            'password' => 'password123',
            'role' => UserRole::Coordinator->value,
        ])
        ->assertForbidden();
});

it('parish admin can update a user', function () {
    $user = User::factory()->create(['parish_id' => $this->parish->id]);
    $user->assignRole(UserRole::Coordinator->value);

    $this->actingAs($this->admin)
        ->putJson("/api/users/{$user->id}", [
            'name' => 'Nome Atualizado',
            'email' => $user->email,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nome Atualizado');
});

it('parish admin can delete a user', function () {
    $user = User::factory()->create(['parish_id' => $this->parish->id]);
    $user->assignRole(UserRole::Coordinator->value);

    $this->actingAs($this->admin)
        ->deleteJson("/api/users/{$user->id}")
        ->assertOk();

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

it('cannot delete own user', function () {
    $this->actingAs($this->admin)
        ->deleteJson("/api/users/{$this->admin->id}")
        ->assertStatus(422);
});

it('parish admin can toggle user active status', function () {
    $user = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $user->assignRole(UserRole::Coordinator->value);

    $this->actingAs($this->admin)
        ->patchJson("/api/users/{$user->id}/toggle-active")
        ->assertOk()
        ->assertJsonPath('data.active', false);
});

it('cannot toggle own active status', function () {
    $this->actingAs($this->admin)
        ->patchJson("/api/users/{$this->admin->id}/toggle-active")
        ->assertStatus(422);
});

it('cannot access users from another parish', function () {
    $otherDiocese = Diocese::factory()->create();
    $otherSector = Sector::factory()->create(['diocese_id' => $otherDiocese->id]);
    $otherParish = Parish::factory()->create(['sector_id' => $otherSector->id]);
    $otherUser = User::factory()->create(['parish_id' => $otherParish->id]);
    $otherUser->assignRole(UserRole::Coordinator->value);

    $this->actingAs($this->admin)
        ->getJson("/api/users/{$otherUser->id}")
        ->assertForbidden();
});
