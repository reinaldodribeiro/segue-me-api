<?php

namespace App\Exceptions;

use RuntimeException;

class IncompatiblePersonTypeException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('O tipo desta pessoa não é compatível com o tipo aceito pela equipe.');
    }
}
