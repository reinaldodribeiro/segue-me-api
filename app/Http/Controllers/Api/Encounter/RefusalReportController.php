<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefusalReportController extends Controller
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function __invoke(Request $request, string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $refusals = TeamMember::query()
            ->with(['person:id,name,type,phone', 'team:id,name'])
            ->refused()
            ->whereHas('team', fn ($q) => $q->where('encounter_id', $encounterId))
            ->orderBy('responded_at', 'desc')
            ->get();

        $byTeam = $refusals
            ->groupBy(fn ($m) => $m->team->name)
            ->map(fn ($members, $teamName) => [
                'team' => $teamName,
                'count' => $members->count(),
                'refusals' => $members->map(fn ($m) => [
                    'person_id' => $m->person_id,
                    'person_name' => $m->person->name,
                    'person_type' => $m->person->type->value,
                    'reason' => $m->refusal_reason,
                    'responded_at' => $m->responded_at?->toISOString(),
                ]),
            ])
            ->values();

        return response()->json([
            'data' => [
                'encounter_id' => $encounterId,
                'total_refusals' => $refusals->count(),
                'by_team' => $byTeam,
            ],
        ]);
    }
}
