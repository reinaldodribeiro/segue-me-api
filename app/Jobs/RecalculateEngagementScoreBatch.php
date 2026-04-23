<?php

namespace App\Jobs;

use App\Domain\People\Services\EngagementScoreCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Batch-recalculate engagement scores for multiple people in a single job.
 *
 * Use this job instead of firing individual RecalculateEngagementScore
 * listener calls when many people are affected at once (e.g. encounter
 * completed, spreadsheet import). It fetches all aggregate metrics in
 * two queries regardless of batch size.
 */
class RecalculateEngagementScoreBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * @param  array<string>  $personIds  UUIDs of people whose scores need recalculation.
     */
    public function __construct(
        private readonly array $personIds,
    ) {}

    public function handle(EngagementScoreCalculator $calculator): void
    {
        $calculator->recalculateBatch($this->personIds);
    }
}
