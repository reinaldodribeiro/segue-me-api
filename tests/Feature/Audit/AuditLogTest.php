<?php

use App\Domain\Audit\Models\AuditLog;
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

it('parish admin can list audit logs', function () {
    AuditLog::create([
        'user_id' => $this->admin->id,
        'action' => 'person.deleted',
        'description' => 'Pessoa removida.',
        'created_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->getJson('/api/audit-logs')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

it('coordinator cannot access audit logs', function () {
    $this->actingAs($this->coord)
        ->getJson('/api/audit-logs')
        ->assertForbidden();
});

it('parish admin only sees logs from own parish users', function () {
    $otherDiocese = Diocese::factory()->create();
    $otherSector = Sector::factory()->create(['diocese_id' => $otherDiocese->id]);
    $otherParish = Parish::factory()->create(['sector_id' => $otherSector->id]);
    $otherAdmin = User::factory()->create(['parish_id' => $otherParish->id, 'active' => true]);
    $otherAdmin->assignRole(UserRole::ParishAdmin->value);

    AuditLog::create([
        'user_id' => $otherAdmin->id,
        'action' => 'person.deleted',
        'description' => 'Pessoa de outra paróquia removida.',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/audit-logs')
        ->assertOk();

    expect($response->json('meta.total'))->toBe(0);
});

it('can filter audit logs by action', function () {
    AuditLog::create([
        'user_id' => $this->admin->id,
        'action' => 'person.deleted',
        'description' => 'Pessoa removida.',
        'created_at' => now(),
    ]);
    AuditLog::create([
        'user_id' => $this->admin->id,
        'action' => 'encounter.updated',
        'description' => 'Encontro atualizado.',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/audit-logs?action=person.deleted')
        ->assertOk();

    expect($response->json('meta.total'))->toBe(1);
});
