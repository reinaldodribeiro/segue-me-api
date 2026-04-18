<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Encounter\Models\Team;
use App\Support\Enums\PersonType;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\TeamMemberRole;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Support\Collection;

class MemberSuggestionPrompt
{
    /**
     * @return array{0: string, 1: array<int, string>} [prompt, indexMap]
     *                                                 indexMap: position → person UUID
     */
    public static function build(Team $team, Collection $availablePeople, string $role = 'member'): array
    {
        $acceptedType = $team->accepted_type;
        $isCoordinator = $role === 'coordinator';

        $active = $team->members->filter(
            fn ($m) => $m->person && $m->status !== TeamMemberStatus::Refused
        );

        $youthCoordsFilled = $active->filter(fn ($m) => $m->role === TeamMemberRole::Coordinator && $m->person->type === PersonType::Youth)->count();
        $coupleCoordsFilled = $active->filter(fn ($m) => $m->role === TeamMemberRole::Coordinator && $m->person->type === PersonType::Couple)->count();

        $needYouthCoords = max(0, ($team->coordinators_youth ?? 0) - $youthCoordsFilled);
        $needCoupleCoords = max(0, ($team->coordinators_couples ?? 0) - $coupleCoordsFilled);

        $youthMembersFilled = $active->filter(fn ($m) => $m->role !== TeamMemberRole::Coordinator && $m->person->type === PersonType::Youth)->count();
        $coupleMembersFilled = $active->filter(fn ($m) => $m->role !== TeamMemberRole::Coordinator && $m->person->type === PersonType::Couple)->count();

        $totalCoordSlots = ($team->coordinators_youth ?? 0) + ($team->coordinators_couples ?? 0);
        $openMemberSlots = max(0, $team->max_members - $active->count() - max(0, $totalCoordSlots - $youthCoordsFilled - $coupleCoordsFilled));

        $sort = fn ($p) => $p->engagement_score - ($p->recentRefusalsCount() * 10);

        // Pre-sort and cap at 10 per type — already the best candidates
        $youthCandidates = $availablePeople->filter(fn ($p) => $p->type === PersonType::Youth)->sortByDesc($sort)->take(10)->values();
        $coupleCandidates = $availablePeople->filter(fn ($p) => $p->type === PersonType::Couple)->sortByDesc($sort)->take(10)->values();

        // Build a flat index map: integer position → UUID
        $indexMap = [];
        $idx = 0;
        foreach ($youthCandidates as $p) {
            $indexMap[$idx++] = $p->id;
        }
        $startCouple = $idx;
        foreach ($coupleCandidates as $p) {
            $indexMap[$idx++] = $p->id;
        }

        if ($isCoordinator) {
            $includeYouth = $needYouthCoords > 0;
            $includeCouple = $needCoupleCoords > 0;
            $task = 'Sugira coordenadores.';
            $typeNote = match (true) {
                $includeYouth && $includeCouple => "Precisa: {$needYouthCoords} jovem(ns) e {$needCoupleCoords} casal(ais).",
                $includeYouth => "Somente jovens. Vagas: {$needYouthCoords}.",
                $includeCouple => "Somente casais. Vagas: {$needCoupleCoords}.",
                default => 'Sem vagas.',
            };
        } else {
            $includeYouth = in_array($acceptedType, [TeamAcceptedType::Youth,  TeamAcceptedType::All]);
            $includeCouple = in_array($acceptedType, [TeamAcceptedType::Couple, TeamAcceptedType::All]);
            $task = 'Sugira integrantes.';
            $typeNote = match (true) {
                ! $includeCouple => 'Somente jovens.',
                ! $includeYouth => 'Somente casais.',
                default => 'Mistura proporcional.',
            };
        }

        $compact = fn (Collection $col, int $offset) => $col->map(function ($p, $localIdx) use ($offset) {
            $skills = implode(',', array_slice((array) ($p->skills ?? []), 0, 5));

            return sprintf(
                '{"i":%d,"n":"%s","s":"%s","e":%d,"r":%d}',
                $offset + $localIdx,
                addslashes(strtok($p->name, ' ')),
                $skills,
                (int) $p->engagement_score,
                (int) $p->recentRefusalsCount(),
            );
        })->implode(',');

        $sections = [];
        if (($includeYouth ?? false) && $youthCandidates->isNotEmpty()) {
            $sections[] = 'JOVENS:'.$compact($youthCandidates, 0);
        }
        if (($includeCouple ?? false) && $coupleCandidates->isNotEmpty()) {
            $sections[] = 'CASAIS:'.$compact($coupleCandidates, $startCouple);
        }

        if (empty($sections) || empty($indexMap)) {
            return ['', []];
        }

        $team_info = json_encode([
            'eq' => $team->name,
            'sk' => $team->recommended_skills ?? [],
            'vg' => $isCoordinator ? ($needYouthCoords + $needCoupleCoords) : $openMemberSlots,
            'jm' => $youthMembersFilled,
            'cm' => $coupleMembersFilled,
        ], JSON_UNESCAPED_UNICODE);

        $candidatesText = implode("\n", $sections);

        // i=índice, n=nome, s=habilidades(csv), e=engajamento, r=recusas_recentes
        $prompt = <<<PROMPT
        {$task} {$typeNote}
        EQUIPE:{$team_info}
        CANDIDATOS(i=idx,n=nome,s=habilidades,e=engajamento,r=recusas):
        {$candidatesText}
        Priorize: habilidades compatíveis > engajamento alto > poucas recusas.
        Retorne SOMENTE JSON sem texto extra:
        {"suggestions":[{"i":0,"r":"motivo breve"}]}
        PROMPT;

        return [$prompt, $indexMap];
    }
}
