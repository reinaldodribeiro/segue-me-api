<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\AI\Prompts\ReplacementSuggestionPrompt;
use App\Domain\AI\Services\ClaudeService;
use App\Domain\Encounter\Models\Team;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;

class SuggestReplacement
{
    public function __construct(
        private readonly ClaudeService $claude,
        private readonly PersonRepositoryInterface $people,
    ) {}

    public function execute(Team $team): array
    {
        return Cache::remember(
            CacheKey::replacementSuggestions($team->id),
            now()->addHours(2),
            function () use ($team) {
                $available = $this->people->findAvailableForEncounter($team->encounter_id);

                if ($available->isEmpty()) {
                    return [];
                }

                [$prompt, $indexMap] = ReplacementSuggestionPrompt::build($team, $available);

                if (empty($prompt) || empty($indexMap)) {
                    return [];
                }

                $result = $this->claude->completeAsJson(
                    $prompt,
                    [],
                    'claude-haiku-4-5-20251001',
                    'replacement_suggestion',
                    ['team_id' => $team->id, 'encounter_id' => $team->encounter_id],
                );

                return collect($result['suggestions'] ?? [])
                    ->filter(fn ($s) => isset($s['i']) && isset($indexMap[$s['i']]))
                    ->map(fn ($s) => [
                        'person_id' => $indexMap[$s['i']],
                        'reason' => $s['r'] ?? '',
                    ])
                    ->values()
                    ->all();
            }
        );
    }
}
