<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->roleName(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'active' => $this->active,
            'parish_id' => $this->parish_id,
            'sector_id' => $this->sector_id,
            'diocese_id' => $this->diocese_id,
            'movement_ids' => DB::table('movement_user')
                ->where('user_id', $this->id)
                ->pluck('movement_id')
                ->values(),
            'parish' => $this->whenLoaded('parish', fn () => [
                'id' => $this->parish->id,
                'name' => $this->parish->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
