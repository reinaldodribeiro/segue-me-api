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

it('lists people for parish admin', function () {
    Person::factory()->count(3)->create(['parish_id' => $this->parish->id]);

    $this->actingAs($this->admin)
        ->getJson('/api/people')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('creates a person', function () {
    $this->actingAs($this->coord)
        ->postJson('/api/people', [
            'type' => PersonType::Youth->value,
            'name' => 'João Silva',
            'phones' => ['(11) 99999-0001'],
            'email' => 'joao@test.com',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'João Silva');
});

it('returns 409 when creating person with similar name and same phone', function () {
    Person::factory()->create([
        'parish_id' => $this->parish->id,
        'name' => 'João Silva',
        'phones' => ['11999990001'],
    ]);

    $this->actingAs($this->coord)
        ->postJson('/api/people', [
            'type' => PersonType::Youth->value,
            'name' => 'João Silva',
            'phones' => ['(11) 99999-0001'],
        ])
        ->assertStatus(409)
        ->assertJsonStructure(['duplicates']);
});

it('creates person with force=true even if duplicate detected', function () {
    Person::factory()->create([
        'parish_id' => $this->parish->id,
        'name' => 'João Silva',
        'phones' => ['11999990001'],
    ]);

    $this->actingAs($this->coord)
        ->postJson('/api/people', [
            'type' => PersonType::Youth->value,
            'name' => 'João Silva',
            'phones' => ['(11) 99999-0001'],
            'force' => true,
        ])
        ->assertCreated();
});

it('shows a person', function () {
    $person = Person::factory()->create(['parish_id' => $this->parish->id]);

    $this->actingAs($this->coord)
        ->getJson("/api/people/{$person->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $person->id);
});

it('updates a person', function () {
    $person = Person::factory()->create(['parish_id' => $this->parish->id]);

    $this->actingAs($this->admin)
        ->putJson("/api/people/{$person->id}", [
            'type' => $person->type->value,
            'name' => 'Nome Atualizado',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nome Atualizado');
});

it('deletes a person (parish admin only)', function () {
    $person = Person::factory()->create(['parish_id' => $this->parish->id]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/people/{$person->id}")
        ->assertOk();

    $this->assertSoftDeleted('people', ['id' => $person->id]);
});

it('prevents coordinator from deleting a person', function () {
    $person = Person::factory()->create(['parish_id' => $this->parish->id]);

    $this->actingAs($this->coord)
        ->deleteJson("/api/people/{$person->id}")
        ->assertForbidden();
});

it('cannot access person from another parish', function () {
    $otherDiocese = Diocese::factory()->create();
    $otherSector = Sector::factory()->create(['diocese_id' => $otherDiocese->id]);
    $otherParish = Parish::factory()->create(['sector_id' => $otherSector->id]);
    $person = Person::factory()->create(['parish_id' => $otherParish->id]);

    $this->actingAs($this->admin)
        ->getJson("/api/people/{$person->id}")
        ->assertNotFound();
});
