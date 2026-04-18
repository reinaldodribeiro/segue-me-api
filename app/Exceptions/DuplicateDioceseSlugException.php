<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateDioceseSlugException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Este slug já está em uso por outra diocese.');
    }
}
