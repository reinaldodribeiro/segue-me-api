<?php

namespace App\Http\Controllers\Api\People;

use App\Domain\Encounter\Actions\SuggestTeamsForPerson;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonSuggestTeamsController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function __invoke(Request $request, string $personId, SuggestTeamsForPerson $action): JsonResponse
    {
        $request->validate([
            'encounter_id' => ['required', 'uuid', 'exists:encounters,id'],
        ]);

        $person = $this->people->findOrFail($personId);
        $encounter = $this->encounters->findOrFail($request->encounter_id);

        $this->authorize('view', $person);

        $suggestions = $action->execute($person, $encounter);

        return response()->json(['data' => $suggestions]);
    }
}
