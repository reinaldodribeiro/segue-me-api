<?php

namespace App\Exceptions;

use RuntimeException;

class PersonAlreadyAllocatedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Esta pessoa já está alocada em uma equipe deste encontro.');
    }
}
