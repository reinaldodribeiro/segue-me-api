<?php

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Jobs\ProcessFichaOcr;
use App\Jobs\ProcessSpreadsheetImport;
use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
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

    Storage::fake('local');
    Queue::fake();
});

it('dispatches spreadsheet import job and returns 202', function () {
    $file = UploadedFile::fake()->create('pessoas.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($this->coord)
        ->postJson('/api/people/import/spreadsheet', ['file' => $file])
        ->assertStatus(202)
        ->assertJsonStructure(['message', 'cache_key']);

    Queue::assertPushed(ProcessSpreadsheetImport::class);
});

it('rejects invalid file type on spreadsheet import', function () {
    $file = UploadedFile::fake()->create('pessoas.pdf', 100, 'application/pdf');

    $this->actingAs($this->coord)
        ->postJson('/api/people/import/spreadsheet', ['file' => $file])
        ->assertUnprocessable();
});

it('dispatches OCR import job and returns 202', function () {
    $file = UploadedFile::fake()->create('ficha.jpg', 200, 'image/jpeg');

    $this->actingAs($this->coord)
        ->postJson('/api/people/import/scan', ['file' => $file])
        ->assertStatus(202)
        ->assertJsonStructure(['message', 'cache_key']);

    Queue::assertPushed(ProcessFichaOcr::class);
});

it('returns processing status when cache key has no result', function () {
    $this->actingAs($this->coord)
        ->getJson('/api/people/import/status?cache_key=non-existent-key')
        ->assertOk()
        ->assertJsonPath('status', 'processing');
});

it('returns done status when cache key has result', function () {
    $key = 'import:test-key';
    Cache::put($key, ['status' => 'done', 'imported' => 5, 'errors' => []], 60);

    $this->actingAs($this->coord)
        ->getJson("/api/people/import/status?cache_key={$key}")
        ->assertOk()
        ->assertJsonPath('status', 'done');
});
