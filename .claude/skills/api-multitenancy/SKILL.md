---
name: api-multitenancy
description: "Pattern for multi-tenant models scoped by parish_id in the segue-me API.
  BelongsToParish trait applies ParishScope globally. HasUuid provides UUID primary keys. All DTOs carry parish_id from request user.
  Use when creating a new tenant-scoped model, adding parish isolation to a query, or the user says 'new model', 'parish scope', 'multi-tenant', 'scoped to parish', 'tenant isolation'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Multi-tenancy Pattern

Every parish-scoped model uses `BelongsToParish` trait → auto-applies `ParishScope` global scope on all queries. `HasUuid` provides UUID primary keys. `SoftDeletes` for archivable entities.

## Pattern

- Add traits: `use BelongsToParish, HasUuid, SoftDeletes;`
- `BelongsToParish` → triggers `ParishScope` automatically on `booted()`
- `ParishScope` adds `WHERE parish_id = ?` using authenticated user's parish
- Admin cross-parish queries: use `->withoutGlobalScope(ParishScope::class)`
- `parish_id` flows: `$request->user()->parish_id` → DTO → Action → repository `create()`
- Never access `auth()` at domain or infrastructure layer

## Example

```php
class Encounter extends Model {
    use BelongsToParish, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = ['parish_id', 'movement_id', 'name', 'status', ...];

    protected function casts(): array {
        return ['status' => EncounterStatus::class, 'date' => 'date'];
    }
}
```
Ref: `app/Domain/Encounter/Models/Encounter.php`
Ref: `app/Support/Traits/BelongsToParish.php`
Ref: `app/Infrastructure/Scopes/ParishScope.php`

## References

For full examples with variants:
→ Read `references/examples.md`
