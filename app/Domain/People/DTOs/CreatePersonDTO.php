<?php

namespace App\Domain\People\DTOs;

use App\Http\Requests\People\StorePersonRequest;
use App\Support\Enums\PersonType;

final readonly class CreatePersonDTO
{
    public function __construct(
        public string $parishId,
        public PersonType $type,
        public string $name,
        public ?string $partnerName,
        public ?string $photo,
        public ?string $birthDate,
        public ?string $partnerBirthDate,
        public ?string $weddingDate,
        public ?string $email,
        public array $skills,
        public ?string $notes,
        public ?int $encounterYear = null,
        // Common new fields
        public ?string $nickname = null,
        public ?string $address = null,
        public ?string $birthplace = null,
        public ?array $phones = null,
        public ?string $churchMovement = null,
        public ?string $receivedAt = null,
        public ?string $encounterDetails = null,
        // Youth-only fields
        public ?string $fatherName = null,
        public ?string $motherName = null,
        public ?string $educationLevel = null,
        public ?string $educationStatus = null,
        public ?string $course = null,
        public ?string $institution = null,
        public ?array $sacraments = null,
        public ?string $availableSchedule = null,
        public ?string $musicalInstruments = null,
        public ?string $talksTestimony = null,
        // Couple-only fields
        public ?string $partnerNickname = null,
        public ?string $partnerBirthplace = null,
        public ?string $partnerEmail = null,
        public ?array $partnerPhones = null,
        public ?string $partnerPhoto = null,
        public ?array $homePhones = null,
    ) {}

    public static function fromRequest(StorePersonRequest $request): self
    {
        return new self(
            parishId: $request->user()->parish_id,
            type: PersonType::from($request->validated('type')),
            name: $request->validated('name'),
            partnerName: $request->validated('partner_name'),
            photo: null, // upload tratado separadamente
            birthDate: $request->validated('birth_date'),
            partnerBirthDate: $request->validated('partner_birth_date'),
            weddingDate: $request->validated('wedding_date'),
            email: $request->validated('email'),
            skills: $request->validated('skills', []),
            notes: $request->validated('notes'),
            encounterYear: $request->filled('encounter_year') ? (int) $request->validated('encounter_year') : null,
            nickname: $request->validated('nickname'),
            address: $request->validated('address'),
            birthplace: $request->validated('birthplace'),
            phones: $request->validated('phones'),
            churchMovement: $request->validated('church_movement'),
            receivedAt: $request->validated('received_at'),
            encounterDetails: $request->validated('encounter_details'),
            fatherName: $request->validated('father_name'),
            motherName: $request->validated('mother_name'),
            educationLevel: $request->validated('education_level'),
            educationStatus: $request->validated('education_status'),
            course: $request->validated('course'),
            institution: $request->validated('institution'),
            sacraments: $request->validated('sacraments'),
            availableSchedule: $request->validated('available_schedule'),
            musicalInstruments: $request->validated('musical_instruments'),
            talksTestimony: $request->validated('talks_testimony'),
            partnerNickname: $request->validated('partner_nickname'),
            partnerBirthplace: $request->validated('partner_birthplace'),
            partnerEmail: $request->validated('partner_email'),
            partnerPhones: $request->validated('partner_phones'),
            partnerPhoto: null, // upload tratado separadamente
            homePhones: $request->validated('home_phones'),
        );
    }
}
