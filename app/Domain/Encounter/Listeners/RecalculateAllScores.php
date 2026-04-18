<?php

namespace App\Domain\Encounter\Listeners;

use App\Domain\Encounter\Events\EncounterCompleted;
use App\Domain\People\Models\Person;
use App\Domain\People\Services\EngagementScoreCalculator;

class RecalculateAllScores
{
    public function __construct(
        private readonly EngagementScoreCalculator $calculator,
    ) {}

    public function handle(EncounterCompleted $event): void
    {
        $personIds = $event->encounter->teamMembers()
            ->pluck('person_id')
            ->unique();

        Person::whereIn('id', $personIds)->each(
            fn (Person $person) => $this->calculator->recalculateAndSave($person)
        );
    }
}
