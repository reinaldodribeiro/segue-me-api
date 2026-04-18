<?php

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Domain\People\Models\Person;
use App\Models\User;
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

it('returns engagement report structure for a parish', function () {
    Person::factory()->count(5)->create([
        'parish_id' => $this->parish->id,
        'active' => true,
        'type' => PersonType::Youth,
        'engagement_score' => 50,
    ]);

    $this->actingAs($this->admin)
        ->getJson("/api/parishes/{$this->parish->id}/report/engagement")
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'parish_id',
                'total_active',
                'by_level',
                'average_score',
                'top_20',
            ],
        ]);
});

it('coordinator can view engagement report', function () {
    $this->actingAs($this->coord)
        ->getJson("/api/parishes/{$this->parish->id}/report/engagement")
        ->assertOk();
});

it('returns correct engagement level counts', function () {
    Person::factory()->create([
        'parish_id' => $this->parish->id,
        'active' => true,
        'type' => PersonType::Youth,
        'engagement_score' => 70,
    ]);
    Person::factory()->create([
        'parish_id' => $this->parish->id,
        'active' => true,
        'type' => PersonType::Youth,
        'engagement_score' => 5,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/parishes/{$this->parish->id}/report/engagement")
        ->assertOk();

    expect($response->json('data.by_level.destaque'))->toBe(1);
    expect($response->json('data.by_level.baixo'))->toBe(1);
});
