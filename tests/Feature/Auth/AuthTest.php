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
});

function makeParish(): Parish
{
    $diocese = Diocese::factory()->create();
    $sector = Sector::factory()->create(['diocese_id' => $diocese->id]);

    return Parish::factory()->create(['sector_id' => $sector->id]);
}

function makeUser(string $role, ?Parish $parish = null): User
{
    $parish ??= makeParish();
    $user = User::factory()->create([
        'parish_id' => $parish->id,
        'password' => bcrypt('password'),
        'active' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

it('returns token on valid login', function () {
    $user = makeUser(UserRole::Coordinator->value);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('returns 422 on invalid credentials', function () {
    $user = makeUser(UserRole::Coordinator->value);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

it('returns 422 for inactive user', function () {
    $user = makeUser(UserRole::Coordinator->value);
    $user->update(['active' => false]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertUnprocessable();
});

it('returns authenticated user on me endpoint', function () {
    $user = makeUser(UserRole::ParishAdmin->value);

    $this->actingAs($user)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

it('logs out and invalidates token', function () {
    $user = makeUser(UserRole::Coordinator->value);

    $this->actingAs($user)
        ->postJson('/api/auth/logout')
        ->assertOk();
});

it('rejects unauthenticated requests', function () {
    $this->getJson('/api/auth/me')->assertUnauthorized();
});
