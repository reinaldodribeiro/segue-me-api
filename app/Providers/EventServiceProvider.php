<?php

namespace App\Providers;

use App\Domain\Encounter\Events\EncounterCompleted;
use App\Domain\Encounter\Events\PersonAllocated;
use App\Domain\Encounter\Events\TeamMemberConfirmed;
use App\Domain\Encounter\Events\TeamMemberRefused;
use App\Domain\Encounter\Listeners\ConvertParticipantsToPeople;
use App\Domain\Encounter\Listeners\DispatchEvaluationTokens;
use App\Domain\Encounter\Listeners\RecalculateAllScores;
use App\Domain\Encounter\Listeners\RecalculateEngagementScore;
use App\Domain\People\Events\PersonCreated;
use App\Domain\People\Listeners\DetectDuplicatesOnImport;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PersonAllocated::class => [
            RecalculateEngagementScore::class,
        ],
        TeamMemberConfirmed::class => [
            RecalculateEngagementScore::class,
        ],
        TeamMemberRefused::class => [
            RecalculateEngagementScore::class,
        ],
        EncounterCompleted::class => [
            RecalculateAllScores::class,
            DispatchEvaluationTokens::class,
            ConvertParticipantsToPeople::class,
        ],
        PersonCreated::class => [
            DetectDuplicatesOnImport::class,
        ],
    ];
}
