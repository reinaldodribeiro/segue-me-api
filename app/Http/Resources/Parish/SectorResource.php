<?php

namespace App\Http\Resources\Parish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'diocese_id' => $this->diocese_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'active' => $this->active,
            'diocese' => $this->whenLoaded('diocese', fn () => [
                'id' => $this->diocese->id,
                'name' => $this->diocese->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
