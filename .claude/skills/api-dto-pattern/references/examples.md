<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# DTO Pattern — Examples

## Create DTO with required and optional fields

```php
final readonly class CreateEncounterDTO {
    public function __construct(
        public string  $parishId,
        public string  $movementId,
        public string  $responsibleUserId,
        public string  $name,
        public ?int    $editionNumber,
        public string  $date,
        public int     $durationDays,
        public ?string $location,
        public ?int    $maxParticipants,
    ) {}

    public static function fromRequest(StoreEncounterRequest $request): self {
        return new self(
            parishId:          $request->user()->parish_id,
            movementId:        $request->validated('movement_id'),
            responsibleUserId: $request->validated('responsible_user_id', $request->user()->id),
            name:              $request->validated('name'),
            editionNumber:     $request->validated('edition_number'),
            date:              $request->validated('date'),
            durationDays:      (int) ($request->validated('duration_days') ?? 1),
            location:          $request->validated('location'),
            maxParticipants:   $request->filled('max_participants') ? (int) $request->validated('max_participants') : null,
        );
    }
}
```
Ref: `app/Domain/Encounter/DTOs/CreateEncounterDTO.php`

## Update DTO (partial update)

```php
final readonly class UpdateEncounterDTO {
    public static function fromRequest(UpdateEncounterRequest $request): self {
        return new self(
            name:   $request->validated('name'),
            status: EncounterStatus::from($request->validated('status')),
        );
    }
}
```
Ref: `app/Domain/Encounter/DTOs/UpdateEncounterDTO.php`
