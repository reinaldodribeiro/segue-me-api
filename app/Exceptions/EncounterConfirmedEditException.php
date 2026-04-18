<?php

namespace App\Exceptions;

use RuntimeException;

class EncounterConfirmedEditException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Encontros confirmados só permitem alteração de status.');
    }
}
