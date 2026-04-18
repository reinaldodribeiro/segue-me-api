<?php

namespace App\Domain\Encounter\Listeners;

use App\Domain\Encounter\Actions\GenerateEvaluationTokens;
use App\Domain\Encounter\Events\EncounterCompleted;

class DispatchEvaluationTokens
{
    public function __construct(
        private readonly GenerateEvaluationTokens $generateTokens,
    ) {}

    public function handle(EncounterCompleted $event): void
    {
        $this->generateTokens->execute($event->encounter);
    }
}
