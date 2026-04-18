<?php

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (UserRole::cases() as $role) {
        Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }

    $diocese = Diocese::factory()->create();
    $sector = Sector::factory()->create(['diocese_id' => $diocese->id]);
    $parish = Parish::factory()->create(['sector_id' => $sector->id]);
    $this->coord = User::factory()->create(['parish_id' => $parish->id, 'active' => true]);
    $this->coord->assignRole(UserRole::Coordinator->value);
});

it('returns processing status when job is not done', function () {
    $this->actingAs($this->coord)
        ->getJson('/api/jobs/status?cache_key=unknown-key')
        ->assertOk()
        ->assertJsonPath('status', 'processing')
        ->assertJsonPath('data', null);
});

it('returns done status with data when job is complete', function () {
    $key = 'narrative:test-encounter-id';
    $data = ['status' => 'done', 'text' => 'Narrativa gerada com sucesso.'];
    Cache::put($key, $data, 300);

    $this->actingAs($this->coord)
        ->getJson("/api/jobs/status?cache_key={$key}")
        ->assertOk()
        ->assertJsonPath('status', 'done')
        ->assertJsonPath('data.status', 'done');
});

it('requires cache_key parameter', function () {
    $this->actingAs($this->coord)
        ->getJson('/api/jobs/status')
        ->assertUnprocessable();
});

it('requires authentication', function () {
    $this->getJson('/api/jobs/status?cache_key=any-key')
        ->assertUnauthorized();
});
