<?php

namespace App\Http\Resources\People;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonAvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $confirmedMembers = $this->teamMembers
            ->where('status', 'confirmed');

        $refusedMembers = $this->teamMembers
            ->where('status', 'refused')
            ->sortByDesc('created_at')
            ->take(3);

        $pastTeamsFromMembers = $confirmedMembers->pluck('team.name')->filter();
        $pastTeamsFromExperiences = $this->whenLoaded('teamExperiences',
            fn () => $this->teamExperiences->pluck('team_name')->filter(),
            collect()
        );

        $pastTeams = $pastTeamsFromMembers->merge($pastTeamsFromExperiences)->unique()->values();

        $pastMovementTeamIds = $confirmedMembers
            ->pluck('team.movement_team_id')
            ->merge(
                $this->whenLoaded('teamExperiences',
                    fn () => $this->teamExperiences->pluck('movement_team_id')->filter(),
                    collect()
                )
            )
            ->filter()
            ->unique()
            ->values();

        $recentRefusalsCount = $refusedMembers->count();

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'name' => $this->name,
            'partner_name' => $this->partner_name,
            'photo' => $this->photo,
            'phones' => $this->phones ?? [],
            'email' => $this->email,
            'skills' => $this->skills ?? [],
            'engagement_score' => $this->engagement_score,
            'engagement_level' => $this->engagementLevel(),
            'active' => $this->active,
            'encounter_year' => $this->encounter_year,

            // Indicadores de compatibilidade (RF-22)
            'past_teams' => $pastTeams,
            'past_movement_team_ids' => $pastMovementTeamIds,
            'recent_refusals_count' => $recentRefusalsCount,
            'consecutive_refusals_alert' => $recentRefusalsCount >= 2,
        ];
    }

    private function engagementLevel(): string
    {
        return match (true) {
            $this->engagement_score >= 60 => 'destaque',
            $this->engagement_score >= 30 => 'alto',
            $this->engagement_score >= 10 => 'medio',
            default => 'baixo',
        };
    }
}
