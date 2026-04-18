<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Encounter\Models\TeamEvaluation;

class TeamAnalysisPrompt
{
    public static function build(TeamEvaluation $eval): string
    {
        $members = [];
        foreach ($eval->memberEvaluations as $me) {
            $entry = [
                'n' => strtok($me->person->name, ' '),
                'c' => $me->commitment_rating,
                'r' => match ($me->fulfilled_responsibilities) {
                    'yes' => 'S', 'partially' => 'P', default => 'N',
                },
                'rec' => match ($me->recommend) {
                    'yes' => 'S', 'with_reservations' => 'R', default => 'N',
                },
            ];
            if ($me->positive_highlight) {
                $entry['pos'] = $me->positive_highlight;
            }
            if ($me->issue_observed) {
                $entry['neg'] = $me->issue_observed;
            }
            $members[] = $entry;
        }

        $metrics = array_filter([
            'pr' => $eval->preparation_rating,
            'te' => $eval->teamwork_rating,
            'ma' => $eval->materials_rating,
            'og' => $eval->overall_team_rating,
            'prc' => $eval->preparation_comment ?: null,
            'tec' => $eval->teamwork_comment ?: null,
            'mac' => $eval->materials_comment ?: null,
            'pb' => $eval->issues_text ?: null,
            'ml' => $eval->improvements_text ?: null,
        ]);

        $data = json_encode(
            ['metrics' => $metrics, 'members' => $members],
            JSON_UNESCAPED_UNICODE,
        );

        $teamName = $eval->team->name;

        return <<<PROMPT
        Analise a equipe pastoral "{$teamName}". Escreva em pt-BR, 2-3 parágrafos, citando notas e dados reais.
        Chaves: pr/te/ma/og=notas1-5, prc/tec/mac=comentários, pb=problemas, ml=melhorias
        Membros: n=nome, c=comprometimento1-5, r=resp(S/P/N), rec=recomenda(S/R/N), pos=destaque, neg=problema
        DADOS: {$data}
        Retorne SOMENTE JSON: {"analysis":"texto"}
        PROMPT;
    }
}
