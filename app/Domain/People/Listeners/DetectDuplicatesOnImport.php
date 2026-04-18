<?php

namespace App\Domain\People\Listeners;

use App\Domain\People\Actions\DetectDuplicates;
use App\Domain\People\Events\PersonCreated;

class DetectDuplicatesOnImport
{
    public function __construct(
        private readonly DetectDuplicates $detect,
    ) {}

    public function handle(PersonCreated $event): void
    {
        // Reservado para notificação futura quando detectar duplicatas
        $this->detect->execute(
            $event->person->name,
            $event->person->phones[0] ?? null,
            $event->person->email,
            $event->person->parish_id,
        );
    }
}
