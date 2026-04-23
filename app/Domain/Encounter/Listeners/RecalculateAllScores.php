<?php

namespace App\Domain\Encounter\Listeners;

use App\Domain\Encounter\Events\EncounterCompleted;
use App\Jobs\RecalculateEngagementScoreBatch;

class RecalculateAllScores
{
    public function handle(EncounterCompleted $event): void
    {
        $personIds = $event->encounter->teamMembers()
            ->pluck('person_id')
            ->unique()
            ->values()
            ->all();

        if (empty($personIds)) {
            return;
        }

        RecalculateEngagementScoreBatch::dispatch($personIds);
    }
}
