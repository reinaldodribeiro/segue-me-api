<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Repository Pattern — Examples

## Full repository implementation

```php
class EloquentEncounterRepository implements EncounterRepositoryInterface {
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator {
        $query = Encounter::with(['movement', 'responsibleUser'])->orderBy('date', 'desc');
        if (!empty($filters['status']))      $query->where('status', $filters['status']);
        if (!empty($filters['movement_id'])) $query->where('movement_id', $filters['movement_id']);
        if (array_key_exists('movement_ids', $filters)) $query->whereIn('movement_id', $filters['movement_ids']);
        return $query->paginate($perPage);
    }

    public function findOrFail(string $id): Encounter { return Encounter::findOrFail($id); }
    public function create(array $data): Encounter    { return Encounter::create($data); }
    public function update(Encounter $e, array $data): Encounter { $e->update($data); return $e->refresh(); }
    public function delete(Encounter $e): void        { $e->delete(); }
}
```
Ref: `app/Infrastructure/Repositories/EloquentEncounterRepository.php`

## Service Provider binding

```php
// In RepositoryServiceProvider::register()
$this->app->bind(EncounterRepositoryInterface::class, EloquentEncounterRepository::class);
$this->app->bind(PersonRepositoryInterface::class, EloquentPersonRepository::class);
```
Ref: `app/Infrastructure/Providers/RepositoryServiceProvider.php`
