<?php

namespace App\Domain\People\Events;

use App\Domain\People\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
    ) {}
}
