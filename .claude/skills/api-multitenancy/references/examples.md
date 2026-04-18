<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Multi-tenancy — Examples

## Model with all standard traits

```php
class Encounter extends Model {
    use BelongsToParish, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'parish_id', 'movement_id', 'responsible_user_id',
        'name', 'edition_number', 'date', 'duration_days', 'location', 'status',
    ];

    protected function casts(): array {
        return ['status' => EncounterStatus::class, 'date' => 'date', 'duration_days' => 'integer'];
    }
}
```
Ref: `app/Domain/Encounter/Models/Encounter.php`

## BelongsToParish trait

```php
trait BelongsToParish {
    protected static function bootBelongsToParish(): void {
        static::addGlobalScope(new ParishScope());
    }
}
```
Ref: `app/Support/Traits/BelongsToParish.php`

## Parish ID in DTO (correct pattern)

```php
// CORRECT — from request user
public static function fromRequest(StoreEncounterRequest $request): self {
    return new self(parishId: $request->user()->parish_id, ...);
}
// WRONG — never do this inside DTO or Action:
// $parishId = auth()->user()->parish_id;
```
Ref: `app/Domain/Encounter/DTOs/CreateEncounterDTO.php`
