<?php

namespace App\Jobs;

use App\Domain\AI\Prompts\EncounterAnalysisPrompt;
use App\Domain\AI\Services\ClaudeService;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\EncounterAnalysis;
use App\Support\Enums\AnalysisStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateEncounterAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public array $backoff = [65, 130];

    public function __construct(
        private readonly string $encounterId,
    ) {}

    public function handle(ClaudeService $claude): void
    {
        $encounter = Encounter::with([
            'movement',
            'evaluations.memberEvaluations.person',
            'evaluations.team',
        ])->findOrFail($this->encounterId);

        $analysis = $encounter->analysis ?? EncounterAnalysis::create([
            'encounter_id' => $encounter->id,
            'status' => AnalysisStatus::Generating,
        ]);

        try {
            $prompt = EncounterAnalysisPrompt::build($encounter);

            $result = $claude->completeAsJson(
                $prompt,
                [],
                null,
                'encounter_analysis',
                ['encounter_id' => $this->encounterId],
                90,
                1500,
            );

            $analysis->update([
                'general_analysis' => $result['general_analysis'] ?? '',
                'status' => AnalysisStatus::Completed,
                'generated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateEncounterAnalysis failed', [
                'encounter_id' => $this->encounterId,
                'error' => $e->getMessage(),
            ]);

            $analysis->update(['status' => AnalysisStatus::Failed]);

            throw $e;
        }
    }
}
