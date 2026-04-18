<?php

use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\MovementTeam;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Models\User;
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
    $this->admin = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->admin->assignRole(UserRole::ParishAdmin->value);
    $this->coord = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->coord->assignRole(UserRole::Coordinator->value);
    $this->movement = Movement::factory()->create();
});

it('lists templates of a movement', function () {
    MovementTeam::factory()->count(3)->create(['movement_id' => $this->movement->id]);

    $this->actingAs($this->coord)
        ->getJson("/api/movements/{$this->movement->id}/teams")
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('parish admin can create a movement team template', function () {
    $this->actingAs($this->admin)
        ->postJson("/api/movements/{$this->movement->id}/teams", [
            'name' => 'Equipe de Secretaria',
            'min_members' => 2,
            'max_members' => 4,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Equipe de Secretaria');
});

it('coordinator cannot create a movement team template', function () {
    $this->actingAs($this->coord)
        ->postJson("/api/movements/{$this->movement->id}/teams", [
            'name' => 'Equipe',
            'min_members' => 1,
            'max_members' => 3,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertForbidden();
});

it('parish admin can update a movement team template', function () {
    $template = MovementTeam::factory()->create([
        'movement_id' => $this->movement->id,
        'name' => 'Antigo Nome',
        'min_members' => 1,
        'max_members' => 3,
        'accepted_type' => TeamAcceptedType::All,
    ]);

    $this->actingAs($this->admin)
        ->putJson("/api/movements/{$this->movement->id}/teams/{$template->id}", [
            'name' => 'Nome Atualizado',
            'min_members' => 2,
            'max_members' => 5,
            'accepted_type' => TeamAcceptedType::Youth->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nome Atualizado');
});

it('parish admin can delete a movement team template', function () {
    $template = MovementTeam::factory()->create(['movement_id' => $this->movement->id]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/movements/{$this->movement->id}/teams/{$template->id}")
        ->assertOk();

    $this->assertDatabaseMissing('movement_teams', ['id' => $template->id]);
});

it('parish admin can reorder movement team templates', function () {
    $t1 = MovementTeam::factory()->create(['movement_id' => $this->movement->id, 'order' => 0]);
    $t2 = MovementTeam::factory()->create(['movement_id' => $this->movement->id, 'order' => 1]);

    $this->actingAs($this->admin)
        ->postJson("/api/movements/{$this->movement->id}/teams/reorder", [
            'order' => [$t2->id, $t1->id],
        ])
        ->assertOk();

    expect($t1->fresh()->order)->toBe(1)
        ->and($t2->fresh()->order)->toBe(0);
});
