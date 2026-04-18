---
name: api-repository-pattern
description: "Pattern for repository interfaces and Eloquent implementations in the segue-me API.
  Repository interfaces live in Domain/, Eloquent implementations in Infrastructure/Repositories/, bound in RepositoryServiceProvider.
  Use when adding a new data access method, creating a new entity's repository, implementing query filters, or the user says 'add repository', 'new repo', 'query method', 'find by', 'paginate'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Repository Pattern

Interface declared in `Domain/{Domain}/Repositories/`. Eloquent implementation in `Infrastructure/Repositories/`. Bound in `Infrastructure/Providers/RepositoryServiceProvider` via `$this->app->bind(Interface::class, Implementation::class)`.

## Pattern

- Interface: `App\Domain\{Domain}\Repositories\{Entity}RepositoryInterface`
- Implementation: `App\Infrastructure\Repositories\Eloquent{Entity}Repository`
- Standard methods: `paginate(array $filters, int $perPage)`, `findOrFail(string $id)`, `create(array $data)`, `update(Model $entity, array $data)`, `delete(Model $entity)`
- Pagination uses Laravel `LengthAwarePaginator`
- Filters applied in implementation via conditional `$query->where(...)` blocks
- Global scopes (ParishScope) apply automatically via the model

## Example

```php
// Interface
interface EncounterRepositoryInterface {
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function findOrFail(string $id): Encounter;
    public function create(array $data): Encounter;
    public function update(Encounter $encounter, array $data): Encounter;
    public function delete(Encounter $encounter): void;
}

// Implementation (key method)
public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator {
    $query = Encounter::with(['movement', 'responsibleUser'])->orderBy('date', 'desc');
    if (!empty($filters['status'])) $query->where('status', $filters['status']);
    return $query->paginate($perPage);
}
```
Ref: `app/Domain/Encounter/Repositories/EncounterRepositoryInterface.php`
Ref: `app/Infrastructure/Repositories/EloquentEncounterRepository.php`

## References

For full examples with variants:
→ Read `references/examples.md`
