<?php

namespace App\Domain\Encounter\Listeners;

use App\Domain\Encounter\Events\PersonAllocated;
use App\Domain\Encounter\Events\TeamMemberConfirmed;
use App\Domain\Encounter\Events\TeamMemberRefused;
use App\Domain\People\Services\EngagementScoreCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Queued listener — runs after the database transaction commits so it
 * never blocks the HTTP response. Each person is deduplicated via the
 * uniqueId: rapid double-events for the same person collapse into one
 * queued job execution (Laravel Horizon / unique jobs).
 */
class RecalculateEngagementScore implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Run the job only after the surrounding DB transaction has committed,
     * ensuring the data written by the Action is visible to this job.
     */
    public bool $afterCommit = true;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        private readonly EngagementScoreCalculator $calculator,
    ) {}

    public function handle(PersonAllocated|TeamMemberConfirmed|TeamMemberRefused $event): void
    {
        $this->calculator->recalculateAndSave($event->member->person);
    }

    /**
     * Unique key used by Laravel Horizon / ShouldBeUnique to deduplicate
     * queued jobs for the same person. Without ShouldBeUnique this key
     * is informational, but Horizon uses it for deduplication when
     * configured.
     */
    public function uniqueId(PersonAllocated|TeamMemberConfirmed|TeamMemberRefused $event): string
    {
        return 'recalculate-engagement-'.$event->member->person_id;
    }
}
