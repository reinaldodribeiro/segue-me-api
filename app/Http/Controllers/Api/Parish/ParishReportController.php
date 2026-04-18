<?php

namespace App\Http\Controllers\Api\Parish;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use App\Domain\People\Models\Person;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParishReportController extends Controller
{
    public function __construct(
        private readonly ParishRepositoryInterface $parishes,
    ) {}

    public function engagement(Request $request, string $parishId): JsonResponse
    {
        $this->parishes->findOrFail($parishId);

        $query = Person::query()
            ->where('parish_id', $parishId)
            ->where('active', true)
            ->withCount([
                'teamMembers as confirmed_count' => fn ($q) => $q->where('status', 'confirmed'),
                'teamMembers as refused_count' => fn ($q) => $q->where('status', 'refused'),
            ])
            ->orderBy('engagement_score', 'desc');

        // Coordinators: only people who participated in their accessible movements
        $movementIds = $request->user()->accessibleMovementIds();
        if ($movementIds !== null) {
            $encounterIds = Encounter::whereIn('movement_id', $movementIds)->pluck('id');
            $query->whereHas('teamMembers', fn ($q) => $q->whereHas('team', fn ($tq) => $tq->whereIn('encounter_id', $encounterIds)
            )
            );
        }

        $people = $query->get(['id', 'name', 'type', 'partner_name', 'engagement_score']);

        $levels = [
            'destaque' => $people->filter(fn ($p) => $p->engagement_score >= 60)->count(),
            'alto' => $people->filter(fn ($p) => $p->engagement_score >= 30 && $p->engagement_score < 60)->count(),
            'medio' => $people->filter(fn ($p) => $p->engagement_score >= 10 && $p->engagement_score < 30)->count(),
            'baixo' => $people->filter(fn ($p) => $p->engagement_score < 10)->count(),
        ];

        $ranked = $people->take(20)->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'type' => $p->type->value,
            'partner_name' => $p->partner_name,
            'engagement_score' => $p->engagement_score,
            'engagement_level' => match (true) {
                $p->engagement_score >= 60 => 'destaque',
                $p->engagement_score >= 30 => 'alto',
                $p->engagement_score >= 10 => 'medio',
                default => 'baixo',
            },
            'confirmed_participations' => $p->confirmed_count,
            'total_refusals' => $p->refused_count,
        ]);

        return response()->json([
            'data' => [
                'parish_id' => $parishId,
                'total_active' => $people->count(),
                'by_level' => $levels,
                'average_score' => $people->avg('engagement_score') ?? 0,
                'top_20' => $ranked->values(),
            ],
        ]);
    }
}
