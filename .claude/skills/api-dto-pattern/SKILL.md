---
name: api-dto-pattern
description: "Pattern for final readonly DTOs with fromRequest() factory in the segue-me API.
  DTOs carry validated input data from HTTP layer to domain actions. parish_id must be extracted from $request->user(), never from auth().
  Use when creating input objects for actions, passing data across layers, adding new fields to an action, or the user says 'add DTO', 'create data transfer object', 'new input class'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# DTO Pattern

`final readonly` value objects that carry validated input into domain actions. Built from `FormRequest` via static `fromRequest()`. Parish tenancy is always extracted from `$request->user()->parish_id`.

## Pattern

- Namespace: `App\Domain\{Domain}\DTOs\{CreateOrUpdate}{Entity}DTO`
- Declare as `final readonly class`
- Constructor promotes all properties as `public`
- Static `fromRequest(SomeFormRequest $request): self` factory method
- Extract `parish_id` from `$request->user()->parish_id` — never `auth()->user()`
- Use `$request->validated('field')` for all other fields
- Nullable fields use `?type` with `null` default

## Example

```php
final readonly class CreateEncounterDTO {
    public function __construct(
        public string $parishId,
        public string $movementId,
        public string $name,
        public string $date,
        public ?string $location,
    ) {}

    public static function fromRequest(StoreEncounterRequest $request): self {
        return new self(
            parishId:  $request->user()->parish_id,
            movementId: $request->validated('movement_id'),
            name:      $request->validated('name'),
            date:      $request->validated('date'),
            location:  $request->validated('location'),
        );
    }
}
```
Ref: `app/Domain/Encounter/DTOs/CreateEncounterDTO.php`

## References

For full examples with variants:
→ Read `references/examples.md`
