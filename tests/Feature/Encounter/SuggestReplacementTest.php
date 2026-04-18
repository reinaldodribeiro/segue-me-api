<?php

use App\Domain\AI\Services\ClaudeService;
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
    $this->coord->movements()->attach($movement->id);
    $this->encounter = Encounter::factory()->create([
        'movement_id' => $movement->id,
        'parish_id' => $this->parish->id,
        'status' => EncounterStatus::Draft,
    ]);
    $this->team = Team::factory()->create([
        'encounter_id' => $this->encounter->id,
        'accepted_type' => TeamAcceptedType::All,
    ]);
    $this->person = Person::factory()->create([
        'parish_id' => $this->parish->id,
        'type' => PersonType::Youth,
    ]);
    $this->member = TeamMember::factory()->create([
        'team_id' => $this->team->id,
        'person_id' => $this->person->id,
        'status' => TeamMemberStatus::Refused,
    ]);
});

it('returns empty suggestions when no people are available', function () {
    // Make the only person inactive so they don't appear as available
    $this->person->update(['active' => false]);

    $this->actingAs($this->coord)
        ->getJson("/api/team-members/{$this->member->id}/suggest-replacement")
        ->assertOk()
        ->assertJsonPath('data', []);
});

it('returns suggestions from claude when people are available', function () {
    $available = Person::factory()->create([
        'parish_id' => $this->parish->id,
        'type' => PersonType::Youth,
        'active' => true,
    ]);

    $this->mock(ClaudeService::class, function ($mock) {
        $mock->shouldReceive('completeAsJson')
            ->once()
            ->andReturn(['suggestions' => [['i' => 0, 'r' => 'Boa escolha']]]);
    });

    $this->actingAs($this->coord)
        ->getJson("/api/team-members/{$this->member->id}/suggest-replacement")
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
