<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Audit\AuditLogger;
use App\Domain\Encounter\Actions\AllocatePersonToTeam;
use App\Domain\Encounter\Actions\RemovePersonFromTeam;
use App\Domain\Encounter\Actions\SuggestReplacement;
use App\Domain\Encounter\Actions\UpdateMemberStatus;
use App\Domain\Encounter\Repositories\TeamMemberRepositoryInterface;
use App\Domain\Encounter\Repositories\TeamRepositoryInterface;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\AllocatePersonRequest;
use App\Http\Requests\Encounter\UpdateMemberStatusRequest;
use App\Http\Resources\Encounter\TeamMemberResource;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function __construct(
        private readonly TeamMemberRepositoryInterface $members,
        private readonly TeamRepositoryInterface $teams,
        private readonly PersonRepositoryInterface $people,
    ) {}

    public function store(AllocatePersonRequest $request, string $teamId, AllocatePersonToTeam $action): JsonResponse
    {
        $team = $this->teams->findOrFail($teamId);
        $this->authorize('manage', $team);

        $person = $this->people->findOrFail($request->validated('person_id'));

        $member = $action->execute($team, $person, $request->validated('role', 'member'));

        return TeamMemberResource::make($member)
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, string $id, RemovePersonFromTeam $action, AuditLogger $audit): JsonResponse
    {
        $member = $this->members->findOrFail($id);
        $member->load(['person:id,name', 'team.encounter:id,parish_id,status']);
        $this->authorize('manage', $member->team);

        $wasConfirmed = $member->status === TeamMemberStatus::Confirmed;

        $action->execute($member, $request->input('reason'));

        if ($wasConfirmed) {
            $audit->log(
                'team_member.removed_confirmed',
                "Membro confirmado \"{$member->person->name}\" removido da equipe \"{$member->team->name}\".",
                $member,
                ['person_name' => $member->person->name, 'team_name' => $member->team->name, 'reason' => $request->input('reason')]
            );
        }

        return response()->json(['message' => 'Membro removido com sucesso.']);
    }

    public function updateStatus(UpdateMemberStatusRequest $request, string $id, UpdateMemberStatus $action): TeamMemberResource
    {
        $member = $this->members->findOrFail($id);
        $this->authorize('manage', $this->teams->findOrFail($member->team_id));

        $status = TeamMemberStatus::from($request->validated('status'));
        $updated = $action->execute($member, $status, $request->validated('refusal_reason'));

        return TeamMemberResource::make($updated);
    }

    public function suggestReplacement(string $id, SuggestReplacement $action): JsonResponse
    {
        $member = $this->members->findOrFail($id);
        $team = $this->teams->findOrFail($member->team_id);
        $this->authorize('manage', $team);

        $suggestions = $action->execute($team);

        return response()->json(['data' => $suggestions]);
    }
}
