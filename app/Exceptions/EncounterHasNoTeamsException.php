<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class EncounterHasNoTeamsException extends \RuntimeException
{
    public function __construct(string $message = 'O encontro não possui equipes definidas. Configure as equipes antes de confirmar ou concluir.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
