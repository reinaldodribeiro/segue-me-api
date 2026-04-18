<?php

namespace App\Domain\Encounter\DTOs;

use App\Http\Requests\Encounter\UpdateEncounterRequest;
use App\Support\Enums\EncounterStatus;

final readonly class UpdateEncounterDTO
{
    public function __construct(
        public ?string $name,
        public ?string $responsibleUserId,
        public ?string $date,
        public ?int $durationDays,
        public ?string $location,
        public ?EncounterStatus $status,
        public ?int $maxParticipants = null,
    ) {}

    public static function fromRequest(UpdateEncounterRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            responsibleUserId: $request->validated('responsible_user_id'),
            date: $request->validated('date'),
            durationDays: $request->filled('duration_days') ? (int) $request->validated('duration_days') : null,
            location: $request->validated('location'),
            status: $request->filled('status')
                ? EncounterStatus::from($request->validated('status'))
                : null,
            maxParticipants: $request->filled('max_participants') ? (int) $request->validated('max_participants') : null,
        );
    }
}
