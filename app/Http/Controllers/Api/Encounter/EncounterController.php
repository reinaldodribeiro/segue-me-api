<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Audit\AuditLogger;
use App\Domain\Encounter\Actions\CreateEncounter;
use App\Domain\Encounter\Actions\UpdateEncounter;
use App\Domain\Encounter\DTOs\CreateEncounterDTO;
use App\Domain\Encounter\DTOs\UpdateEncounterDTO;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\TeamMember;
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
use Illuminate\Support\Facades\DB;

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

        $encounter->load([
            'movement',
            'responsibleUser',
            'teams.members.person:id,parish_id,type,name,partner_name,photo,birth_date,partner_birth_date,wedding_date,email,skills,notes,engagement_score,active,encounter_year,nickname,address,birthplace,phones,church_movement,received_at,encounter_details,father_name,mother_name,education_level,education_status,course,institution,sacraments,available_schedule,musical_instruments,talks_testimony,partner_nickname,partner_birthplace,partner_email,partner_phones,partner_photo,home_phones,created_at,deleted_at',
        ]);

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

        $allMembers = $encounter->teams->flatMap->members;

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

        $filters = $request->only(['search', 'never_in_movement']);
        $perPage = $request->integer('per_page', 15);

        // Priority filter: people who participated in the previous encounter of the same movement
        // Combines two signals: confirmed team_members from previous encounter + imported people with encounter_year
        if ($request->boolean('priority')) {
            $previousQuery = Encounter::where('movement_id', $encounter->movement_id)
                ->where('id', '!=', $encounter->id);

            if ($encounter->date !== null) {
                $previousQuery->where('date', '<', $encounter->date)->orderByDesc('date');
            } else {
                $previousQuery->orderByDesc('created_at');
            }

            $previous = $previousQuery->first();

            $previousPersonIds = $previous
                ? DB::table('encounter_participants')
                    ->where('encounter_id', $previous->id)
                    ->whereNotNull('converted_to_person_id')
                    ->pluck('converted_to_person_id')
                    ->unique()
                    ->values()
                    ->toArray()
                : [];

            $filters['priority_previous_ids'] = $previousPersonIds;
        }

        $people = $this->people->findAvailableForEncounter($id, $filters, $perPage);

        return PersonAvailabilityResource::collection($people);
    }

    public function previousParticipants(string $id): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('view', $encounter);

        $previousQuery = Encounter::where('movement_id', $encounter->movement_id)
            ->where('id', '!=', $encounter->id);

        if ($encounter->date !== null) {
            $previousQuery->where('date', '<', $encounter->date)->orderByDesc('date');
        } else {
            $previousQuery->orderByDesc('created_at');
        }

        $previous = $previousQuery->first();

        if (! $previous) {
            return response()->json(['data' => []]);
        }

        $personIds = TeamMember::whereHas('team', fn ($q) => $q->where('encounter_id', $previous->id))
            ->where('status', TeamMemberStatus::Confirmed)
            ->pluck('person_id')
            ->unique()
            ->values()
            ->toArray();

        return response()->json(['data' => $personIds]);
    }

    public function resetMembers(string $id): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('update', $encounter);
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        TeamMember::whereIn('team_id', $encounter->teams()->pluck('id'))->delete();

        return response()->json(['message' => 'Todas as equipes foram resetadas.']);
    }
}
