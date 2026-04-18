<?php

namespace App\Domain\People\Actions;

use App\Domain\People\DTOs\UpdatePersonDTO;
use App\Domain\People\Models\Person;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Domain\People\Services\EngagementScoreCalculator;
use Illuminate\Support\Facades\DB;

class UpdatePerson
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly EngagementScoreCalculator $calculator,
    ) {}

    public function execute(Person $person, UpdatePersonDTO $dto): Person
    {
        return DB::transaction(function () use ($person, $dto) {
            $updated = $this->people->update($person, [
                'name' => $dto->name,
                'partner_name' => $dto->partnerName,
                'birth_date' => $dto->birthDate,
                'partner_birth_date' => $dto->partnerBirthDate,
                'wedding_date' => $dto->weddingDate,
                'email' => $dto->email,
                'skills' => $dto->skills,
                'notes' => $dto->notes,
                'active' => $dto->active,
                'encounter_year' => $dto->encounterYear,
                // Common new fields
                'nickname' => $dto->nickname,
                'address' => $dto->address,
                'birthplace' => $dto->birthplace,
                'phones' => $dto->phones,
                'church_movement' => $dto->churchMovement,
                'received_at' => $dto->receivedAt,
                'encounter_details' => $dto->encounterDetails,
                // Youth-only fields
                'father_name' => $dto->fatherName,
                'mother_name' => $dto->motherName,
                'education_level' => $dto->educationLevel,
                'education_status' => $dto->educationStatus,
                'course' => $dto->course,
                'institution' => $dto->institution,
                'sacraments' => $dto->sacraments,
                'available_schedule' => $dto->availableSchedule,
                'musical_instruments' => $dto->musicalInstruments,
                'talks_testimony' => $dto->talksTestimony,
                // Couple-only fields
                'partner_nickname' => $dto->partnerNickname,
                'partner_birthplace' => $dto->partnerBirthplace,
                'partner_email' => $dto->partnerEmail,
                'partner_phones' => $dto->partnerPhones,
                'partner_photo' => $dto->partnerPhoto,
                'home_phones' => $dto->homePhones,
            ]);

            $this->calculator->recalculateAndSave($updated);

            return $updated->refresh();
        });
    }
}
