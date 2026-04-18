<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Encounter\Models\Team;
use Illuminate\Support\Collection;

class ReplacementSuggestionPrompt
{
    /**
     * @return array{0: string, 1: array<int, string>} [prompt, indexMap]
     *                                                 indexMap: position → person UUID
     */
    public static function build(Team $team, Collection $availablePeople): array
    {
        $sort = fn ($p) => $p->engagement_score - ($p->recentRefusalsCount() * 10);

        $candidates = $availablePeople->sortByDesc($sort)->take(15)->values();

        if ($candidates->isEmpty()) {
            return ['', []];
        }

        $indexMap = [];
        $rows = $candidates->map(function ($p, $i) use (&$indexMap) {
            $indexMap[$i] = $p->id;
            $skills = implode(',', array_slice((array) ($p->skills ?? []), 0, 5));
            $type = $p->type->value ?? $p->type;

            return sprintf(
                '{"i":%d,"n":"%s","t":"%s","s":"%s","e":%d,"r":%d}',
                $i,
                addslashes(strtok($p->name, ' ')),
                $type === 'couple' ? 'C' : 'J',
                $skills,
                (int) $p->engagement_score,
                (int) $p->recentRefusalsCount(),
            );
        })->implode(',');

        $team_info = json_encode([
            'eq' => $team->name,
            'sk' => $team->recommended_skills ?? [],
        ], JSON_UNESCAPED_UNICODE);

        // i=idx, n=nome, t=tipo(J=jovem/C=casal), s=habilidades, e=engajamento, r=recusas_recentes
        $prompt = <<<PROMPT
        Pessoa recusou convite. Sugira 5 substitutos.
        EQUIPE:{$team_info}
        CANDIDATOS(i=idx,n=nome,t=J/C,s=habilidades,e=engajamento,r=recusas):
        [{$rows}]
        Priorize: habilidades > poucas recusas > engajamento.
        Retorne SOMENTE JSON:
        {"suggestions":[{"i":0,"r":"motivo breve"}]}
        PROMPT;

        return [$prompt, $indexMap];
    }
}
