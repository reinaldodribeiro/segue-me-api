<?php

namespace App\Exceptions;

use RuntimeException;

class ConfirmedMemberRemovalException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Informe o motivo para remover um membro já confirmado.');
    }
}
