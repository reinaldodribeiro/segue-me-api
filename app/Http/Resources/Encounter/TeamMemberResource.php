<?php

namespace App\Http\Resources\Encounter;

use App\Http\Resources\People\PersonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'refusal_reason' => $this->refusal_reason,
            'invited_at' => $this->invited_at?->format('d/m/Y'),
            'responded_at' => $this->responded_at?->format('d/m/Y'),
            'person' => PersonResource::make($this->whenLoaded('person')),
        ];
    }
}
