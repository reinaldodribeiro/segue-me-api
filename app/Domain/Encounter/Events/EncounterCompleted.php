<?php

namespace App\Domain\Encounter\Events;

use App\Domain\Encounter\Models\Encounter;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EncounterCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Encounter $encounter,
    ) {}
}
