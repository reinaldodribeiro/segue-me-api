<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Audit\AuditLogger;
use App\Domain\Encounter\Actions\CreateEncounter;
use App\Domain\Encounter\Actions\UpdateEncounter;
use App\Domain\Encounter\DTOs\CreateEncounterDTO;
use App\Domain\Encounter\DTOs\UpdateEncounterDTO;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Exceptions\EncounterNotEditableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\StoreEncounterRequest;
use App\Http\Requests\Encounter\UpdateEncounterRequest;
use App\Http\Resources\Encounter\EncounterResource;
use App\Http\Resources\People\PersonAvailabilityResource;
use App\Support\Enums\EncounterStatus;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EncounterController extends Controller
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
        private readonly PersonRepositoryInterface $people,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Encounter::class);

        $filters = $request->only(['status', 'movement_id']);

        $movementIds = $request->user()->accessibleMovementIds();
        if ($movementIds !== null) {
            $filters['movement_ids'] = $movementIds;
        }

        return EncounterResource::collection(
            $this->encounters->paginate($filters)
        );
    }

    public function store(StoreEncounterRequest $request, CreateEncounter $action): JsonResponse
    {
        $this->authorize('create', Encounter::class);

        $encounter = $action->execute(CreateEncounterDTO::fromRequest($request));

        return EncounterResource::make($encounter)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): EncounterResource
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('view', $encounter);

        $encounter->load(['movement', 'responsibleUser', 'teams.members.person']);

        return EncounterResource::make($encounter);
    }

    public function update(UpdateEncounterRequest $request, string $id, UpdateEncounter $action, AuditLogger $audit): EncounterResource
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('update', $encounter);

        $dto = UpdateEncounterDTO::fromRequest($request);
        $updated = $action->execute($encounter, $dto);

        if ($dto->status === EncounterStatus::Completed) {
            $audit->log(
                'encounter.completed',
                "Encontro \"{$encounter->name}\" marcado como concluído.",
                $encounter,
                ['encounter_name' => $encounter->name, 'edition' => $encounter->edition_number]
            );
        }

        return EncounterResource::make($updated);
    }

    public function destroy(string $id): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('delete', $encounter);

        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        $this->encounters->delete($encounter);

        return response()->json(['message' => 'Encontro removido com sucesso.']);
    }

    public function summary(string $id): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('view', $encounter);
        $encounter->load(['teams.members']);

        $teams = $encounter->teams->map(fn ($team) => [
            'id' => $team->id,
            'name' => $team->name,
            'min_members' => $team->min_members,
            'max_members' => $team->max_members,
            'total' => $team->members->whereNotIn('status', [TeamMemberStatus::Refused->value])->count(),
            'confirmed' => $team->members->where('status', TeamMemberStatus::Confirmed->value)->count(),
            'pending' => $team->members->where('status', TeamMemberStatus::Pending->value)->count(),
            'refused' => $team->members->where('status', TeamMemberStatus::Refused->value)->count(),
            'is_full' => $team->isFull(),
            'is_below_minimum' => $team->isBelowMinimum(),
        ]);

        $allMembers = $encounter->teamMembers()->get();

        return response()->json([
            'data' => [
                'encounter_id' => $encounter->id,
                'status' => $encounter->status->value,
                'total_slots' => $encounter->teams->sum('max_members'),
                'total_filled' => $allMembers->whereNotIn('status', [TeamMemberStatus::Refused->value])->count(),
                'total_confirmed' => $allMembers->where('status', TeamMemberStatus::Confirmed->value)->count(),
                'total_pending' => $allMembers->where('status', TeamMemberStatus::Pending->value)->count(),
                'total_refused' => $allMembers->where('status', TeamMemberStatus::Refused->value)->count(),
                'teams' => $teams,
            ],
        ]);
    }

    public function availablePeople(Request $request, string $id): AnonymousResourceCollection
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('view', $encounter);

        $filters = $request->only(['never_in_movement']);
        $people = $this->people->findAvailableForEncounter($id, $filters);

        return PersonAvailabilityResource::collection($people);
    }

    public function resetMembers(string $id): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('update', $encounter);
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        $encounter->teamMembers()->delete();

        return response()->json(['message' => 'Todas as equipes foram resetadas.']);
    }
}
