<?php

namespace App\Http\Resources\People;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $team = $this->team;
        $encounter = $team?->encounter;

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'refusal_reason' => $this->refusal_reason,
            'invited_at' => $this->invited_at?->format('d/m/Y'),
            'responded_at' => $this->responded_at?->format('d/m/Y'),
            'team' => $team ? [
                'id' => $team->id,
                'name' => $team->name,
            ] : null,
            'encounter' => $encounter ? [
                'id' => $encounter->id,
                'name' => $encounter->name,
                'edition_number' => $encounter->edition_number,
                'date' => $encounter->date?->format('d/m/Y'),
                'movement' => $encounter->movement ? [
                    'id' => $encounter->movement->id,
                    'name' => $encounter->movement->name,
                ] : null,
            ] : null,
            'evaluation' => $this->whenLoaded('memberEvaluation', function () {
                $e = $this->memberEvaluation;
                if (! $e) {
                    return null;
                }

                return [
                    'commitment_rating' => $e->commitment_rating,
                    'fulfilled_responsibilities' => $e->fulfilled_responsibilities,
                    'positive_highlight' => $e->positive_highlight,
                    'issue_observed' => $e->issue_observed,
                    'recommend' => $e->recommend,
                ];
            }),
        ];
    }
}
