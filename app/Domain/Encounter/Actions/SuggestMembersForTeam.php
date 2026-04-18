<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\AI\Prompts\MemberSuggestionPrompt;
use App\Domain\AI\Services\ClaudeService;
use App\Domain\Encounter\Models\Team;
use App\Support\CacheKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

                [$prompt, $indexMap] = MemberSuggestionPrompt::build($team->load('members.person'), $available, $role);

                if (empty($prompt) || empty($indexMap)) {
                    return [];
                }

                $result = $this->claude->completeAsJson(
                    $prompt,
                    [],
                    'claude-haiku-4-5-20251001',
                    'member_suggestion',
                    ['team_id' => $team->id, 'encounter_id' => $team->encounter_id, 'role' => $role],
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
