<?php

namespace App\Exceptions;

use RuntimeException;

class TeamFullException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('A equipe atingiu o número máximo de membros.');
    }
}
