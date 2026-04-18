<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateEncounterAnalysis;
use App\Support\Enums\AnalysisStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EncounterAnalysisController extends Controller
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    /**
     * Get the encounter analysis (general + per-team).
     */
    public function show(string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $analysis = $encounter->analysis()->first();

        if (! $analysis) {
            return response()->json([
                'data' => null,
                'message' => 'Nenhuma análise gerada para este encontro.',
            ]);
        }

        $encounter->load(['evaluations.memberEvaluations.person', 'evaluations.team']);

        $teamEvaluations = $encounter->evaluations
            ->filter(fn ($e) => $e->status->value === 'submitted')
            ->sortBy(fn ($e) => $e->team->name)
            ->map(fn ($eval) => [
                'team_id' => $eval->team_id,
                'team_name' => $eval->team->name,
                'preparation_rating' => $eval->preparation_rating,
                'preparation_comment' => $eval->preparation_comment,
                'teamwork_rating' => $eval->teamwork_rating,
                'teamwork_comment' => $eval->teamwork_comment,
                'materials_rating' => $eval->materials_rating,
                'materials_comment' => $eval->materials_comment,
                'overall_team_rating' => $eval->overall_team_rating,
                'issues_text' => $eval->issues_text,
                'improvements_text' => $eval->improvements_text,
                'members' => $eval->memberEvaluations->map(fn ($me) => [
                    'name' => $me->person->name,
                    'commitment_rating' => $me->commitment_rating,
                    'fulfilled_responsibilities' => $me->fulfilled_responsibilities,
                    'recommend' => $me->recommend,
                    'positive_highlight' => $me->positive_highlight,
                    'issue_observed' => $me->issue_observed,
                ])->values(),
            ])
            ->values();

        return response()->json([
            'data' => [
                'id' => $analysis->id,
                'general_analysis' => $analysis->general_analysis,
                'status' => $analysis->status->value,
                'status_label' => $analysis->status->label(),
                'generated_at' => $analysis->generated_at?->toDateTimeString(),
                'team_evaluations' => $teamEvaluations,
            ],
        ]);
    }

    /**
     * Trigger AI analysis generation.
     */
    public function generate(string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        if (! $encounter->isCompleted()) {
            return response()->json([
                'message' => 'O encontro precisa estar concluído.',
            ], 422);
        }

        // Check if there are submitted evaluations
        $submittedCount = $encounter->evaluations()->submitted()->count();
        if ($submittedCount === 0) {
            return response()->json([
                'message' => 'Nenhuma avaliação foi submetida ainda.',
            ], 422);
        }

        // Create or reset analysis record
        $analysis = $encounter->analysis()->firstOrNew(['encounter_id' => $encounter->id]);
        $analysis->status = AnalysisStatus::Generating;
        $analysis->general_analysis = null;
        $analysis->generated_at = null;
        $analysis->save();

        // Clear previous team analyses
        $analysis->teamAnalyses()->delete();

        GenerateEncounterAnalysis::dispatch($encounter->id);

        return response()->json([
            'message' => 'Análise em geração. Acompanhe o progresso na página do encontro.',
        ], 202);
    }

    /**
     * Get evaluation submission progress.
     */
    public function progress(string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $evaluations = $encounter->evaluations()->with('team')->get();

        $total = $evaluations->count();
        $submitted = $evaluations->where('status.value', 'submitted')->count();

        return response()->json([
            'data' => [
                'total_teams' => $total,
                'submitted' => $submitted,
                'pending' => $total - $submitted,
                'teams' => $evaluations->map(fn ($e) => [
                    'team_id' => $e->team_id,
                    'team_name' => $e->team->name,
                    'status' => $e->status->value,
                ]),
            ],
        ]);
    }

    /**
     * Download analysis as PDF.
     */
    public function pdf(string $encounterId): Response
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $analysis = $encounter->analysis()->with('teamAnalyses.team')->firstOrFail();

        if (! $analysis->isCompleted()) {
            abort(422, 'A análise ainda não foi concluída.');
        }

        $encounter->load(['movement', 'teams', 'evaluations.memberEvaluations.person', 'evaluations.team']);
        $parish = $encounter->parish()->first();

        $teamEvaluations = $encounter->evaluations
            ->filter(fn ($e) => $e->status->value === 'submitted')
            ->sortBy(fn ($e) => $e->team->name)
            ->values();

        $pdf = Pdf::loadView('reports.encounter-analysis', [
            'encounter' => $encounter,
            'parish' => $parish,
            'analysis' => $analysis,
            'teamEvaluations' => $teamEvaluations,
        ]);

        return $pdf->download("analise-encontro-{$encounter->edition_number}.pdf");
    }
}
