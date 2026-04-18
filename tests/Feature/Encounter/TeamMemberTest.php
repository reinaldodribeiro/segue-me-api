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

    $diocese = Diocese::factory()->create();
    $sector = Sector::factory()->create(['diocese_id' => $diocese->id]);
    $this->parish = Parish::factory()->create(['sector_id' => $sector->id]);
    $this->coord = User::factory()->create(['parish_id' => $this->parish->id, 'active' => true]);
    $this->coord->assignRole(UserRole::Coordinator->value);

    $movement = Movement::factory()->create();
    $this->encounter = Encounter::factory()->create([
        'movement_id' => $movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Draft,
    ]);
    $this->team = Team::factory()->create([
        'encounter_id' => $this->encounter->id,
        'max_members' => 5,
        'min_members' => 1,
        'accepted_type' => TeamAcceptedType::All,
    ]);
    $this->person = Person::factory()->create([
        'parish_id' => $this->parish->id,
        'type' => PersonType::Youth,
    ]);
});

it('allocates a person to a team', function () {
    $this->actingAs($this->coord)
        ->postJson("/api/teams/{$this->team->id}/members", [
            'person_id' => $this->person->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', TeamMemberStatus::Pending->value);
});

it('prevents allocating same person twice in same encounter', function () {
    TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $this->person->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    $this->actingAs($this->coord)
        ->postJson("/api/teams/{$this->team->id}/members", [
            'person_id' => $this->person->id,
        ])
        ->assertStatus(422);
});

it('updates member status to confirmed', function () {
    $member = TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $this->person->id,
        'status' => TeamMemberStatus::Pending,
    ]);

    $this->actingAs($this->coord)
        ->patchJson("/api/team-members/{$member->id}/status", [
            'status' => TeamMemberStatus::Confirmed->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.status', TeamMemberStatus::Confirmed->value);
});

it('requires reason to remove a confirmed member', function () {
    $member = TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $this->person->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    $this->actingAs($this->coord)
        ->deleteJson("/api/team-members/{$member->id}")
        ->assertStatus(422);
});

it('removes confirmed member with reason', function () {
    $member = TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $this->person->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    $this->actingAs($this->coord)
        ->deleteJson("/api/team-members/{$member->id}", [
            'reason' => 'Indisponível na data.',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
});
