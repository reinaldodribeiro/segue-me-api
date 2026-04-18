<?php

namespace App\Http\Resources\Encounter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncounterParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'partner_name' => $this->partner_name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'phone' => $this->phone,
            'email' => $this->email,
            'birth_date' => $this->birth_date?->format('d/m/Y'),
            'partner_birth_date' => $this->partner_birth_date?->format('d/m/Y'),
            'photo' => $this->photo,
            'converted_to_person_id' => $this->converted_to_person_id,
            'is_converted' => $this->isConverted(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
