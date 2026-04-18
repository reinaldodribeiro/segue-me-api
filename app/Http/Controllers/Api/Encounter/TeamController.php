<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Actions\SuggestMembersForTeam;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Domain\Encounter\Repositories\TeamRepositoryInterface;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Exceptions\EncounterNotEditableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\StoreTeamRequest;
use App\Http\Resources\Encounter\TeamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function __construct(
        private readonly TeamRepositoryInterface $teams,
        private readonly EncounterRepositoryInterface $encounters,
        private readonly PersonRepositoryInterface $people,
    ) {}

    public function index(string $encounterId): AnonymousResourceCollection
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        return TeamResource::collection(
            $this->teams->findByEncounter($encounterId)
        );
    }

    public function store(StoreTeamRequest $request, string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        $team = $this->teams->create([
            'encounter_id' => $encounterId,
            'name' => $request->validated('name'),
            'min_members' => $request->validated('min_members'),
            'max_members' => $request->validated('max_members'),
            'accepted_type' => $request->validated('accepted_type'),
            'recommended_skills' => $request->validated('recommended_skills', []),
            'order' => $request->validated('order', 0),
        ]);

        return TeamResource::make($team->load('members'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): TeamResource
    {
        $team = $this->teams->findOrFail($id);
        $this->authorize('manage', $team);

        return TeamResource::make($team->load('members.person'));
    }

    public function update(StoreTeamRequest $request, string $id): TeamResource
    {
        $team = $this->teams->findOrFail($id);
        $this->authorize('manage', $team);
        throw_if($team->encounter->isCompleted(), EncounterNotEditableException::class);

        $updated = $this->teams->update($team, $request->validated());

        return TeamResource::make($updated->load('members.person'));
    }

    public function destroy(string $id): JsonResponse
    {
        $team = $this->teams->findOrFail($id);
        $this->authorize('manage', $team);
        throw_if($team->encounter->isCompleted(), EncounterNotEditableException::class);

        $this->teams->delete($team);

        return response()->json(['message' => 'Equipe removida com sucesso.']);
    }

    public function suggestMembers(Request $request, string $id, SuggestMembersForTeam $action): JsonResponse
    {
        $team = $this->teams->findOrFail($id);
        $this->authorize('manage', $team);

        $role = in_array($request->query('role'), ['coordinator', 'member']) ? $request->query('role') : 'member';
        $available = $this->people->findAvailableForEncounter($team->encounter_id);
        $suggestions = $action->execute($team, $available, $role);

        return response()->json(['data' => $suggestions]);
    }
}
