<?php

namespace App\Http\Resources\People;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'name' => $this->name,
            'partner_name' => $this->partner_name,
            'photo' => $this->photo,
            'birth_date' => $this->birth_date?->format('d/m/Y'),
            'partner_birth_date' => $this->partner_birth_date?->format('d/m/Y'),
            'wedding_date' => $this->wedding_date?->format('d/m/Y'),
            'email' => $this->email,
            'skills' => $this->skills ?? [],
            'notes' => $this->when(
                $request->user()?->hasAnyRole(['super_admin', 'parish_admin', 'coordinator']),
                $this->notes
            ),
            'engagement_score' => $this->engagement_score,
            'engagement_level' => $this->engagementLevel(),
            'active' => $this->active,
            'encounter_year' => $this->encounter_year,
            // Common new fields
            'nickname' => $this->nickname,
            'address' => $this->address,
            'birthplace' => $this->birthplace,
            'phones' => $this->phones ?? [],
            'church_movement' => $this->church_movement,
            'received_at' => $this->received_at?->format('d/m/Y'),
            'encounter_details' => $this->encounter_details,
            // Youth fields
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'education_level' => $this->education_level,
            'education_status' => $this->education_status,
            'course' => $this->course,
            'institution' => $this->institution,
            'sacraments' => $this->sacraments ?? [],
            'available_schedule' => $this->available_schedule,
            'musical_instruments' => $this->musical_instruments,
            'talks_testimony' => $this->talks_testimony,
            // Couple fields
            'partner_nickname' => $this->partner_nickname,
            'partner_birthplace' => $this->partner_birthplace,
            'partner_email' => $this->partner_email,
            'partner_phones' => $this->partner_phones ?? [],
            'partner_photo' => $this->partner_photo,
            'home_phones' => $this->home_phones ?? [],
            'created_at' => $this->created_at?->toDateTimeString(),
            'parish' => $this->whenLoaded('parish', fn () => [
                'id' => $this->parish->id,
                'name' => $this->parish->name,
            ]),
            'history' => PersonHistoryResource::collection($this->whenLoaded('teamMembers')),
            'team_experiences' => PersonTeamExperienceResource::collection($this->whenLoaded('teamExperiences')),
        ];
    }

    private function engagementLevel(): string
    {
        return match (true) {
            $this->engagement_score >= 60 => 'destaque',
            $this->engagement_score >= 30 => 'alto',
            $this->engagement_score >= 10 => 'medio',
            default => 'baixo',
        };
    }
}
