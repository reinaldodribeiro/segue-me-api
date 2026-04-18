<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncounterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'edition_number' => $this->edition_number,
            'date' => $this->date?->format('d/m/Y'),
            'end_date' => $this->date && $this->duration_days > 1
                                    ? $this->date->copy()->addDays($this->duration_days - 1)->format('d/m/Y')
                                    : null,
            'duration_days' => $this->duration_days ?? 1,
            'location' => $this->location,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'max_participants' => $this->max_participants,
            'participants_count' => $this->whenLoaded('participants', fn () => $this->participants->count(), 0),
            'has_analysis' => $this->whenLoaded('analysis', fn () => $this->analysis?->isCompleted(), false),
            'movement' => $this->whenLoaded('movement', fn () => [
                'id' => $this->movement->id,
                'name' => $this->movement->name,
            ]),
            'responsible_user' => $this->whenLoaded('responsibleUser', fn () => [
                'id' => $this->responsibleUser->id,
                'name' => $this->responsibleUser->name,
            ]),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
