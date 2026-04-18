<?php

use App\Domain\Parish\Actions\CreateDiocese;
use App\Domain\Parish\DTOs\CreateDioceseDTO;
use App\Domain\Parish\Models\Diocese;
use App\Exceptions\DuplicateDioceseSlugException;

it('creates a diocese successfully', function () {
    $dto = new CreateDioceseDTO(
        name: 'Diocese Teste',
        slug: 'diocese-teste',
    );

    $diocese = app(CreateDiocese::class)->execute($dto);

    expect($diocese)->toBeInstanceOf(Diocese::class)
        ->and($diocese->name)->toBe('Diocese Teste')
        ->and($diocese->slug)->toBe('diocese-teste')
        ->and($diocese->active)->toBeTrue();
});

it('throws validation error when slug already exists', function () {
    Diocese::factory()->create(['slug' => 'slug-existente']);

    $dto = new CreateDioceseDTO(
        name: 'Outra Diocese',
        slug: 'slug-existente',
    );

    expect(fn () => app(CreateDiocese::class)->execute($dto))
        ->toThrow(DuplicateDioceseSlugException::class);
});

it('generates slug from name when not provided', function () {
    $dto = new CreateDioceseDTO(
        name: 'Diocese São Paulo',
        slug: '',
    );

    $diocese = app(CreateDiocese::class)->execute($dto);

    expect($diocese->slug)->toBe('diocese-sao-paulo');
});
