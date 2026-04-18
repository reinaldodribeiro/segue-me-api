<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Encounter\Models\Encounter;

class EncounterAnalysisPrompt
{
    public static function build(Encounter $encounter): string
    {
        $encounter->loadMissing([
            'movement',
            'evaluations.memberEvaluations.person',
            'evaluations.team',
        ]);

        $teamsData = [];

        foreach ($encounter->evaluations as $eval) {
            if ($eval->status->value !== 'submitted') {
                continue;
            }

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

            $team = [
                'eq' => $eval->team->name,
                'pr' => $eval->preparation_rating,
                'te' => $eval->teamwork_rating,
                'ma' => $eval->materials_rating,
                'og' => $eval->overall_team_rating,
                'mem' => $members,
            ];
            if ($eval->preparation_comment) {
                $team['prc'] = $eval->preparation_comment;
            }
            if ($eval->teamwork_comment) {
                $team['tec'] = $eval->teamwork_comment;
            }
            if ($eval->issues_text) {
                $team['pb'] = $eval->issues_text;
            }
            if ($eval->improvements_text) {
                $team['ml'] = $eval->improvements_text;
            }

            $teamsData[] = $team;
        }

        $dataJson = json_encode($teamsData, JSON_UNESCAPED_UNICODE);

        $parts = array_filter([
            $encounter->name,
            $encounter->movement?->name,
            $encounter->date?->format('d/m/Y'),
            $encounter->edition_number ? "{$encounter->edition_number}ª ed." : null,
        ]);
        $info = implode(' | ', $parts);

        // Chaves: eq=equipe, pr/te/ma/og=notas1-5, mem=membros, prc/tec=comentários, pb=problemas, ml=melhorias
        // Membros: n=nome, c=comprometimento, r=resp(S/P/N), rec=recomenda(S/R/N), pos=destaque, neg=problema

        return <<<PROMPT
        Escreva a análise GERAL do encontro pastoral abaixo. 3-4 parágrafos, pt-BR, tom profissional e construtivo.
        Identifique padrões, pontos fortes e fracos comuns entre equipes, recomendações para os próximos encontros. Cite dados reais (notas, comentários).
        ENCONTRO: {$info}
        DADOS: {$dataJson}
        Retorne SOMENTE JSON: {"general_analysis":"texto"}
        PROMPT;
    }
}
