<?php

namespace App\Infrastructure\Providers;

use App\Domain\Encounter\Repositories\EncounterParticipantRepositoryInterface;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use App\Domain\Encounter\Repositories\TeamMemberRepositoryInterface;
use App\Domain\Encounter\Repositories\TeamRepositoryInterface;
use App\Domain\Parish\Repositories\DioceseRepositoryInterface;
use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use App\Domain\Parish\Repositories\SectorRepositoryInterface;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Infrastructure\Repositories\EloquentDioceseRepository;
use App\Infrastructure\Repositories\EloquentEncounterParticipantRepository;
use App\Infrastructure\Repositories\EloquentEncounterRepository;
use App\Infrastructure\Repositories\EloquentMovementRepository;
use App\Infrastructure\Repositories\EloquentParishRepository;
use App\Infrastructure\Repositories\EloquentPersonRepository;
use App\Infrastructure\Repositories\EloquentSectorRepository;
use App\Infrastructure\Repositories\EloquentTeamMemberRepository;
use App\Infrastructure\Repositories\EloquentTeamRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        // Parish domain
        DioceseRepositoryInterface::class => EloquentDioceseRepository::class,
        SectorRepositoryInterface::class => EloquentSectorRepository::class,
        ParishRepositoryInterface::class => EloquentParishRepository::class,

        // People domain
        PersonRepositoryInterface::class => EloquentPersonRepository::class,

        // Encounter domain
        MovementRepositoryInterface::class => EloquentMovementRepository::class,
        EncounterRepositoryInterface::class => EloquentEncounterRepository::class,
        TeamRepositoryInterface::class => EloquentTeamRepository::class,
        TeamMemberRepositoryInterface::class => EloquentTeamMemberRepository::class,
        EncounterParticipantRepositoryInterface::class => EloquentEncounterParticipantRepository::class,
    ];
}
