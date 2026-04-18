<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovementTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movement_id' => $this->movement_id,
            'name' => $this->name,
            'icon' => $this->icon,
            'min_members' => $this->min_members,
            'max_members' => $this->max_members,
            'coordinators_youth' => $this->coordinators_youth,
            'coordinators_couples' => $this->coordinators_couples,
            'accepted_type' => $this->accepted_type->value,
            'accepted_type_label' => $this->accepted_type->label(),
            'recommended_skills' => $this->recommended_skills ?? [],
            'order' => $this->order,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
