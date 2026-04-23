<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\AI\Prompts\MemberSuggestionPrompt;
use App\Domain\AI\Services\ClaudeService;
use App\Domain\Encounter\Models\Team;
use App\Support\CacheKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SuggestMembersForTeam
{
    public function __construct(
        private readonly ClaudeService $claude,
    ) {}

    public function execute(Team $team, Collection $available, string $role = 'member'): array
    {
        return Cache::remember(
            CacheKey::teamSuggestions($team->id, $role),
            now()->addHours(2),
            function () use ($team, $available, $role) {
                if ($available->isEmpty()) {
                    return [];
                }

                [$prompt, $indexMap] = MemberSuggestionPrompt::build($team->loadMissing('members.person'), $available, $role);

                if (empty($prompt) || empty($indexMap)) {
                    return [];
                }

                try {
                    $result = $this->claude->completeAsJson(
                        $prompt,
                        [],
                        'claude-haiku-4-5-20251001',
                        'member_suggestion',
                        ['team_id' => $team->id, 'encounter_id' => $team->encounter_id, 'role' => $role],
                        timeout: 10,
                    );
                } catch (\Throwable $e) {
                    Log::warning('SuggestMembersForTeam: Claude API call failed, returning empty suggestions', [
                        'team_id' => $team->id,
                        'role' => $role,
                        'error' => $e->getMessage(),
                    ]);

                    return [];
                }

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
