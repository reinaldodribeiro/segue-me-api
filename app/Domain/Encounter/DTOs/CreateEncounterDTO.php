<?php

namespace App\Domain\Encounter\DTOs;

use App\Http\Requests\Encounter\StoreEncounterRequest;

final readonly class CreateEncounterDTO
{
    public function __construct(
        public string $parishId,
        public string $movementId,
        public string $responsibleUserId,
        public string $name,
        public ?int $editionNumber,
        public string $date,
        public int $durationDays,
        public ?string $location,
        public ?int $maxParticipants,
    ) {}

    public static function fromRequest(StoreEncounterRequest $request): self
    {
        return new self(
            parishId: $request->user()->parish_id,
            movementId: $request->validated('movement_id'),
            responsibleUserId: $request->validated('responsible_user_id', $request->user()->id),
            name: $request->validated('name'),
            editionNumber: $request->validated('edition_number'),
            date: $request->validated('date'),
            durationDays: (int) ($request->validated('duration_days') ?? 1),
            location: $request->validated('location'),
            maxParticipants: $request->filled('max_participants') ? (int) $request->validated('max_participants') : null,
        );
    }
}
