<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Encounter\Models\Encounter;

class GeneralAnalysisPrompt
{
    /**
     * @param  array<array{team_name: string, analysis: string}>  $teamAnalyses
     */
    public static function build(Encounter $encounter, array $teamAnalyses): string
    {
        $parts = array_filter([
            $encounter->name,
            $encounter->movement?->name,
            $encounter->date?->format('d/m/Y'),
            $encounter->edition_number ? "{$encounter->edition_number}ª ed." : null,
        ]);
        $info = implode(' | ', $parts);

        $summaries = json_encode($teamAnalyses, JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
        Com base nas análises individuais das equipes abaixo, escreva a análise GERAL do encontro em 3-4 parágrafos (pt-BR).
        Identifique padrões, pontos fortes e fracos transversais, recomendações para próximos encontros. Seja específico.
        ENCONTRO: {$info}
        ANÁLISES: {$summaries}
        Retorne SOMENTE JSON: {"general_analysis":"texto"}
        PROMPT;
    }
}
