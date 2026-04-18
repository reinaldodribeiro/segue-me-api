---
name: api-action-pattern
description: "Pattern for Laravel DDD Action classes with execute() method in the segue-me API.
  Actions are single-purpose use-case classes that receive loaded entities (never IDs), enforce business rules via domain exceptions, and wrap mutations in DB::transaction.
  Use when creating a new use case, adding business logic, implementing a new operation, or the user says 'add action', 'new use case', 'implement logic', 'create action class'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Action Pattern

Single-purpose class with `execute()`. Injected into controller method params or resolved via `app(SomeAction::class)`. Receives **fully loaded entities** — never IDs. Enforces business rules by throwing domain exceptions.

## Pattern

- Namespace: `App\Domain\{Domain}\Actions\{ActionName}`
- Constructor injects repository interfaces only
- `execute()` accepts domain objects; returns domain object or primitive
- Business rule violations → throw named domain exception (never `ValidationException`)
- State mutations wrapped in `DB::transaction()`
- Fire domain events after successful mutation
- Cache invalidation inside transaction

## Example

```php
class AllocatePersonToTeam {
    public function __construct(private readonly TeamMemberRepositoryInterface $members) {}

    public function execute(Team $team, Person $person, string $role = 'member'): TeamMember {
        throw_if($team->encounter->isCompleted(), EncounterNotEditableException::class);
        throw_if($team->isFull(), TeamFullException::class);
        return DB::transaction(function () use ($team, $person, $role) {
            $member = $this->members->create([...]);
            Cache::forget(CacheKey::teamSuggestions($team->id));
            event(new PersonAllocated($member));
            return $member->load('person');
        });
    }
}
```
Ref: `app/Domain/Encounter/Actions/AllocatePersonToTeam.php`

## References

For full examples with variants:
→ Read `references/examples.md`
