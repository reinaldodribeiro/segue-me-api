<?php

namespace App\Domain\Encounter\Listeners;

use App\Domain\Encounter\Events\EncounterCompleted;
use App\Domain\Encounter\Models\EncounterParticipant;
use App\Domain\People\Models\Person;
use Illuminate\Support\Facades\DB;

class ConvertParticipantsToPeople
{
    public function handle(EncounterCompleted $event): void
    {
        $encounter = $event->encounter;
        $year = $encounter->date?->year ?? now()->year;
        $parishId = $encounter->parish_id;

        $participants = EncounterParticipant::where('encounter_id', $encounter->id)
            ->whereNull('converted_to_person_id')
            ->get();

        foreach ($participants as $participant) {
            DB::transaction(function () use ($participant, $parishId, $year) {
                $person = $this->findOrCreatePerson($participant, $parishId, $year);

                $participant->update(['converted_to_person_id' => $person->id]);
            });
        }
    }

    private function findOrCreatePerson(EncounterParticipant $participant, string $parishId, int $year): Person
    {
        // Primary match: name + birth_date (case-insensitive)
        $existing = null;

        if ($participant->birth_date) {
            $existing = Person::where('parish_id', $parishId)
                ->whereRaw('LOWER(name) = ?', [strtolower($participant->name)])
                ->where('birth_date', $participant->birth_date)
                ->first();
        }

        // Fallback: email, then phone (check JSON phones array)
        if (! $existing && $participant->email) {
            $existing = Person::where('parish_id', $parishId)
                ->where('email', $participant->email)
                ->first();
        }

        if (! $existing && $participant->phone) {
            $existing = Person::where('parish_id', $parishId)
                ->whereJsonContains('phones', $participant->phone)
                ->first();
        }

        if ($existing) {
            // Update encounter_year if not already set (or update to most recent)
            if (! $existing->encounter_year || $year > $existing->encounter_year) {
                $existing->update(['encounter_year' => $year]);
            }

            return $existing;
        }

        return Person::create([
            'parish_id' => $parishId,
            'type' => $participant->type->value,
            'name' => $participant->name,
            'partner_name' => $participant->partner_name,
            'phones' => $participant->phone ? [$participant->phone] : null,
            'email' => $participant->email,
            'birth_date' => $participant->birth_date,
            'partner_birth_date' => $participant->partner_birth_date,
            'photo' => $participant->photo,
            'encounter_year' => $year,
            'active' => true,
            'engagement_score' => 0,
        ]);
    }
}
