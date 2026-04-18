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

it('lists skills of a parish', function () {
    $this->parish->update(['available_skills' => ['Música', 'Canto']]);

    $this->actingAs($this->coord)
        ->getJson("/api/parishes/{$this->parish->id}/skills")
        ->assertOk()
        ->assertJsonPath('data.0', 'Música');
});

it('parish admin can add a skill', function () {
    $this->actingAs($this->admin)
        ->postJson("/api/parishes/{$this->parish->id}/skills", [
            'skill' => 'Pregação',
        ])
        ->assertCreated()
        ->assertJsonPath('data.0', 'Pregação');
});

it('returns 409 when skill already exists', function () {
    $this->parish->update(['available_skills' => ['Música']]);

    $this->actingAs($this->admin)
        ->postJson("/api/parishes/{$this->parish->id}/skills", [
            'skill' => 'Música',
        ])
        ->assertStatus(409);
});

it('parish admin can remove a skill', function () {
    $this->parish->update(['available_skills' => ['Música', 'Canto']]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/parishes/{$this->parish->id}/skills", [
            'skill' => 'Música',
        ])
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
