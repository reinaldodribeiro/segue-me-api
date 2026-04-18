<?php

namespace App\Http\Resources\People;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonTeamExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movement_team_id' => $this->movement_team_id,
            'team_name' => $this->team_name,
            'team_icon' => $this->movementTeam?->icon,
            'role' => $this->role,
            'role_label' => $this->role === 'coordinator' ? 'Coordenador' : 'Integrante',
            'year' => $this->year,
        ];
    }
}
