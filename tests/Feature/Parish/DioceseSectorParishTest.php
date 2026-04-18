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

    // Super Admin sem paróquia vinculada
    $this->superAdmin = User::factory()->create(['parish_id' => null, 'active' => true]);
    $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

    $this->admin = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->admin->assignRole(UserRole::ParishAdmin->value);
});

// ── Diocese ────────────────────────────────────────────────────────────────

it('super admin can list dioceses', function () {
    Diocese::factory()->count(2)->create();

    $this->actingAs($this->superAdmin)
        ->getJson('/api/dioceses')
        ->assertOk();
});

it('super admin can create a diocese', function () {
    $this->actingAs($this->superAdmin)
        ->postJson('/api/dioceses', [
            'name' => 'Diocese de Teste',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Diocese de Teste');
});

it('super admin can update a diocese', function () {
    $diocese = Diocese::factory()->create();

    $this->actingAs($this->superAdmin)
        ->putJson("/api/dioceses/{$diocese->id}", [
            'name' => 'Diocese Atualizada',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Diocese Atualizada');
});

it('super admin can delete a diocese', function () {
    $diocese = Diocese::factory()->create();

    $this->actingAs($this->superAdmin)
        ->deleteJson("/api/dioceses/{$diocese->id}")
        ->assertOk();

    $this->assertSoftDeleted('dioceses', ['id' => $diocese->id]);
});

it('parish admin cannot delete a diocese', function () {
    $diocese = Diocese::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson("/api/dioceses/{$diocese->id}")
        ->assertForbidden();
});

// ── Sector ─────────────────────────────────────────────────────────────────

it('super admin can create a sector', function () {
    $diocese = Diocese::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson("/api/dioceses/{$diocese->id}/sectors", [
            'name' => 'Setor Norte',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Setor Norte');
});

it('super admin can update a sector', function () {
    $diocese = Diocese::factory()->create();
    $sector = Sector::factory()->create(['diocese_id' => $diocese->id]);

    $this->actingAs($this->superAdmin)
        ->putJson("/api/sectors/{$sector->id}", [
            'name' => 'Setor Atualizado',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Setor Atualizado');
});

// ── Parish ─────────────────────────────────────────────────────────────────

it('super admin can create a parish', function () {
    $sector = Sector::factory()->create();

    $this->actingAs($this->superAdmin)
        ->postJson("/api/sectors/{$sector->id}/parishes", [
            'name' => 'Paróquia São José',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Paróquia São José');
});

it('parish admin can view own parish', function () {
    $this->actingAs($this->admin)
        ->getJson("/api/parishes/{$this->parish->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $this->parish->id);
});
