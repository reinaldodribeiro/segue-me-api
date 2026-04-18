<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Actions\GenerateEvaluationTokens;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\TeamEvaluation;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Encounter\TeamEvaluationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class EvaluationTokenController extends Controller
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    /**
     * List evaluation tokens for all teams in an encounter.
     */
    public function index(string $encounterId): AnonymousResourceCollection
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $evaluations = $encounter->evaluations()->with('team')->get();

        return TeamEvaluationResource::collection($evaluations);
    }

    /**
     * Generate evaluation tokens for all teams in an encounter.
     */
    public function generate(string $encounterId, GenerateEvaluationTokens $action): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        if (! $encounter->isCompleted()) {
            return response()->json([
                'message' => 'O encontro precisa estar concluído para gerar links de avaliação.',
            ], 422);
        }

        $evaluations = $action->execute($encounter);
        $evaluations->each(fn ($e) => $e->loadMissing('team'));

        return TeamEvaluationResource::collection($evaluations)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Regenerate token and PIN for a specific team.
     */
    public function regenerate(string $encounterId, string $teamId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        $evaluation = TeamEvaluation::where('encounter_id', $encounterId)
            ->where('team_id', $teamId)
            ->firstOrFail();

        if ($evaluation->isSubmitted()) {
            return response()->json([
                'message' => 'Não é possível regenerar o link de uma avaliação já submetida.',
            ], 422);
        }

        $evaluation->update([
            'token' => Str::uuid()->toString(),
            'pin' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'expires_at' => now()->addDays(30),
        ]);

        $evaluation->load('team');

        return TeamEvaluationResource::make($evaluation)
            ->response()
            ->setStatusCode(200);
    }
}
