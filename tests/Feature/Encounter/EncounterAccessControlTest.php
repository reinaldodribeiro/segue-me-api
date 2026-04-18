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

    // Paróquia A
    $dioceseA = Diocese::factory()->create();
    $sectorA = Sector::factory()->create(['diocese_id' => $dioceseA->id]);
    $this->parishA = Parish::factory()->create(['sector_id' => $sectorA->id]);
    $this->coordA = User::factory()->create(['parish_id' => $this->parishA->id, 'active' => true]);
    $this->coordA->assignRole(UserRole::Coordinator->value);

    $movementA = Movement::factory()->create();
    $this->coordA->movements()->attach($movementA->id);
    $this->encounterA = Encounter::factory()->create([
        'movement_id' => $movementA->id,
        'parish_id' => $this->parishA->id,
        'status' => EncounterStatus::Draft,
    ]);
    $this->teamA = Team::factory()->create([
        'encounter_id' => $this->encounterA->id,
        'max_members' => 5,
        'min_members' => 1,
        'accepted_type' => TeamAcceptedType::All,
    ]);
    $this->personA = Person::factory()->create([
        'parish_id' => $this->parishA->id,
        'type' => PersonType::Youth,
    ]);

    // Paróquia B
    $dioceseB = Diocese::factory()->create();
    $sectorB = Sector::factory()->create(['diocese_id' => $dioceseB->id]);
    $this->parishB = Parish::factory()->create(['sector_id' => $sectorB->id]);
    $this->coordB = User::factory()->create(['parish_id' => $this->parishB->id, 'active' => true]);
    $this->coordB->assignRole(UserRole::Coordinator->value);

    $movementB = Movement::factory()->create();
    $this->coordB->movements()->attach($movementB->id);
    $this->encounterB = Encounter::factory()->create([
        'movement_id' => $movementB->id,
        'parish_id' => $this->parishB->id,
        'status' => EncounterStatus::Draft,
    ]);
    $this->teamB = Team::factory()->create([
        'encounter_id' => $this->encounterB->id,
        'max_members' => 5,
        'min_members' => 1,
        'accepted_type' => TeamAcceptedType::All,
    ]);
});

// ── Isolamento multi-tenant: Equipes ───────────────────────────────────────
// Rotas baseadas em /encounters/{id}/... retornam 404 porque o ParishScope
// filtra o encounter da outra paróquia antes da policy ser avaliada.
// Rotas rasas /teams/{id} retornam 403 porque o team é encontrado mas
// a TeamPolicy nega o acesso ao comparar parish_id.

it('coordinator cannot list teams of another parish encounter', function () {
    $this->actingAs($this->coordA)
        ->getJson("/api/encounters/{$this->encounterB->id}/teams")
        ->assertNotFound();
});

it('coordinator cannot add a team to another parish encounter', function () {
    $this->actingAs($this->coordA)
        ->postJson("/api/encounters/{$this->encounterB->id}/teams", [
            'name' => 'Invasora',
            'min_members' => 1,
            'max_members' => 3,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertNotFound();
});

it('coordinator cannot update a team from another parish', function () {
    $this->actingAs($this->coordA)
        ->putJson("/api/teams/{$this->teamB->id}", [
            'name' => 'Hackeada',
            'min_members' => 1,
            'max_members' => 3,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertForbidden();
});

it('coordinator cannot delete a team from another parish', function () {
    $this->actingAs($this->coordA)
        ->deleteJson("/api/teams/{$this->teamB->id}")
        ->assertForbidden();
});

// ── Isolamento multi-tenant: Membros ──────────────────────────────────────

it('coordinator cannot allocate person to team of another parish', function () {
    $this->actingAs($this->coordA)
        ->postJson("/api/teams/{$this->teamB->id}/members", [
            'person_id' => $this->personA->id,
        ])
        ->assertForbidden();
});

it('coordinator cannot update member status from another parish', function () {
    $member = TeamMember::factory()->create([
        'team_id' => $this->teamB->id,
        'person_id' => Person::factory()->create(['parish_id' => $this->parishB->id])->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    $this->actingAs($this->coordA)
        ->patchJson("/api/team-members/{$member->id}/status", [
            'status' => TeamMemberStatus::Confirmed->value,
        ])
        ->assertForbidden();
});

it('coordinator cannot remove member from another parish team', function () {
    $member = TeamMember::factory()->create([
        'team_id' => $this->teamB->id,
        'person_id' => Person::factory()->create(['parish_id' => $this->parishB->id])->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    $this->actingAs($this->coordA)
        ->deleteJson("/api/team-members/{$member->id}")
        ->assertForbidden();
});

// ── State machine do Encounter ─────────────────────────────────────────────

it('cannot revert completed encounter to draft', function () {
    $this->encounterA->update(['status' => EncounterStatus::Completed]);

    $this->actingAs($this->coordA)
        ->putJson("/api/encounters/{$this->encounterA->id}", [
            'status' => EncounterStatus::Draft->value,
        ])
        ->assertStatus(422);
});

it('cannot revert completed encounter to confirmed', function () {
    $this->encounterA->update(['status' => EncounterStatus::Completed]);

    $this->actingAs($this->coordA)
        ->putJson("/api/encounters/{$this->encounterA->id}", [
            'status' => EncounterStatus::Confirmed->value,
        ])
        ->assertStatus(422);
});

it('cannot add team to encounter from another parish even as its coordinator', function () {
    $this->actingAs($this->coordB)
        ->postJson("/api/encounters/{$this->encounterA->id}/teams", [
            'name' => 'Equipe',
            'min_members' => 1,
            'max_members' => 3,
            'accepted_type' => TeamAcceptedType::All->value,
        ])
        ->assertNotFound();
});
