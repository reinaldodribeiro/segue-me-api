<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'team_name' => $this->team->name,
            'team_icon' => $this->team->icon,
            'token' => $this->token,
            'pin' => $this->pin,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'submitted_at' => $this->submitted_at?->toDateTimeString(),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'public_url' => config('app.frontend_url').'/avaliacao/'.$this->token,
        ];
    }
}
