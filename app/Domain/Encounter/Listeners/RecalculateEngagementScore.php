<?php

namespace App\Domain\Encounter\Listeners;

use App\Domain\Encounter\Events\PersonAllocated;
use App\Domain\Encounter\Events\TeamMemberConfirmed;
use App\Domain\Encounter\Events\TeamMemberRefused;
use App\Domain\People\Services\EngagementScoreCalculator;

class RecalculateEngagementScore
{
    public function __construct(
        private readonly EngagementScoreCalculator $calculator,
    ) {}

    public function handle(PersonAllocated|TeamMemberConfirmed|TeamMemberRefused $event): void
    {
        $this->calculator->recalculateAndSave($event->member->person);
    }
}
