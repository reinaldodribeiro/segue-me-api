<?php

namespace App\Http\Resources\Parish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DioceseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'active' => $this->active,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
