<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncounterAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'general_analysis' => $this->general_analysis,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'generated_at' => $this->generated_at?->toDateTimeString(),
            'team_analyses' => $this->whenLoaded('teamAnalyses', fn () => $this->teamAnalyses->map(fn ($ta) => [
                'team_id' => $ta->team_id,
                'team_name' => $ta->team->name,
                'analysis' => $ta->analysis,
            ])
            ),
        ];
    }
}
