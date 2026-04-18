<?php

namespace App\Http\Resources\Parish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParishResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sector_id' => $this->sector_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'skills' => $this->available_skills,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'active' => $this->active,
            'sector' => $this->whenLoaded('sector', fn () => [
                'id' => $this->sector->id,
                'name' => $this->sector->name,
                'diocese_id' => $this->sector->diocese_id,
                'diocese' => $this->sector->relationLoaded('diocese') && $this->sector->diocese ? [
                    'id' => $this->sector->diocese->id,
                    'name' => $this->sector->diocese->name,
                ] : null,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
