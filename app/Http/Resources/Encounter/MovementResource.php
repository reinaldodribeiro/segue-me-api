<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'target_audience' => $this->target_audience->value,
            'target_audience_label' => $this->target_audience->label(),
            'scope' => $this->scope->value,
            'scope_label' => $this->scope->label(),
            'description' => $this->description,
            'active' => $this->active,
            'teams_count' => $this->whenLoaded('movementTeams', fn () => $this->movementTeams->count()),
            'teams' => MovementTeamResource::collection($this->whenLoaded('movementTeams')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
