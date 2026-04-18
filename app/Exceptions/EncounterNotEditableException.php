<?php

namespace App\Exceptions;

use RuntimeException;

class EncounterNotEditableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Encontros com status "realizado" não podem ser editados.');
    }
}
