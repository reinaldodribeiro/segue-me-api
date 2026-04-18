<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $membersCount = $this->whenLoaded('members', fn () => $this->members->whereNotIn('status', ['refused'])->count(), 0);
        $confirmedCount = $this->whenLoaded('members', fn () => $this->members->where('status', 'confirmed')->count(), 0);

        return [
            'id' => $this->id,
            'movement_team_id' => $this->movement_team_id,
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
            'members_count' => $membersCount,
            'confirmed_count' => $confirmedCount,
            'is_full' => $membersCount >= $this->max_members,
            'is_below_minimum' => $confirmedCount < $this->min_members,
            'members' => TeamMemberResource::collection($this->whenLoaded('members')),
        ];
    }
}
