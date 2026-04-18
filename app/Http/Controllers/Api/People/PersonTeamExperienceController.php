<?php

namespace App\Http\Controllers\Api\People;

use App\Domain\Encounter\Models\MovementTeam;
use App\Domain\People\Models\PersonTeamExperience;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Domain\People\Services\EngagementScoreCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\StorePersonTeamExperienceRequest;
use App\Http\Resources\People\PersonTeamExperienceResource;
use Illuminate\Http\JsonResponse;

class PersonTeamExperienceController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly EngagementScoreCalculator $calculator,
    ) {}

    public function index(string $personId): JsonResponse
    {
        $person = $this->people->findOrFail($personId);
        $this->authorize('view', $person);

        return response()->json([
            'data' => PersonTeamExperienceResource::collection(
                $person->teamExperiences()->with('movementTeam')->orderByDesc('year')->orderBy('team_name')->get()
            ),
        ]);
    }

    public function store(StorePersonTeamExperienceRequest $request, string $personId): JsonResponse
    {
        $person = $this->people->findOrFail($personId);
        $this->authorize('update', $person);

        $validated = $request->validated();

        // Overwrite team_name from movement team if linked
        if (! empty($validated['movement_team_id'])) {
            $mt = MovementTeam::find($validated['movement_team_id']);
            if ($mt) {
                $validated['team_name'] = $mt->name;
            }
        }

        $exp = $person->teamExperiences()->create($validated + ['person_id' => $person->id]);
        $exp->load('movementTeam');
        $this->calculator->recalculateAndSave($person);

        return PersonTeamExperienceResource::make($exp)
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(string $personId, string $experienceId): JsonResponse
    {
        $person = $this->people->findOrFail($personId);
        $this->authorize('update', $person);

        $exp = PersonTeamExperience::where('person_id', $personId)->findOrFail($experienceId);
        $exp->delete();
        $this->calculator->recalculateAndSave($person->fresh());

        return response()->json(['message' => 'Experiência removida.']);
    }
}
