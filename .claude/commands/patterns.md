<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Patterns: segue-me API

> Recurring implementation patterns traced from real source files.

## 1. Action Pattern

Single-purpose class with `execute()`. Receives **loaded entities**, never IDs.
Resolved via `app(SomeAction::class)->execute(...)` or injected into controller method.

```php
class AllocatePersonToTeam {
    public function __construct(private readonly TeamMemberRepositoryInterface $members) {}
    public function execute(Team $team, Person $person, string $role = 'member'): TeamMember {
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);
        return DB::transaction(fn() => $this->members->create([...]));
    }
}
```
Ref: `app/Domain/Encounter/Actions/AllocatePersonToTeam.php`

## 2. DTO Pattern

`final readonly` class. Static `fromRequest()` extracts `parish_id` from `$request->user()`.
Never use `auth()` inside a DTO.

```php
final readonly class CreateEncounterDTO {
    public static function fromRequest(StoreEncounterRequest $request): self {
        return new self(parishId: $request->user()->parish_id, ...);
    }
}
```
Ref: `app/Domain/Encounter/DTOs/CreateEncounterDTO.php`

## 3. Controller Pattern (thin)

Constructor injects repositories. Method params inject actions. Resolves entity from repo, authorizes, builds DTO, calls action, returns Resource.

```php
public function store(StoreEncounterRequest $request, CreateEncounter $action): JsonResponse {
    $this->authorize('create', Encounter::class);
    $encounter = $action->execute(CreateEncounterDTO::fromRequest($request));
    return EncounterResource::make($encounter)->response()->setStatusCode(201);
}
```
Ref: `app/Http/Controllers/Api/Encounter/EncounterController.php`

## 4. Repository Pattern

Interface in `Domain/{Domain}/Repositories/`. Eloquent implementation in `Infrastructure/Repositories/`. Bound in `Infrastructure/Providers/RepositoryServiceProvider.php`.

```php
// Interface
interface EncounterRepositoryInterface {
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;
    public function findOrFail(string $id): Encounter;
}
// Implementation
class EloquentEncounterRepository implements EncounterRepositoryInterface { ... }
```
Ref: `app/Domain/Encounter/Repositories/EncounterRepositoryInterface.php`, `app/Infrastructure/Repositories/EloquentEncounterRepository.php`

## 5. Domain Exception Pattern

Extend `\RuntimeException`. Register in `bootstrap/app.php` to return 422 JSON automatically.
Never throw `ValidationException` from domain layer.

```php
// In bootstrap/app.php:
$exceptions->render(function (TeamFullException $e, Request $request) {
    if ($request->is('api/*')) return response()->json(['message' => $e->getMessage()], 422);
});
```
Ref: `bootstrap/app.php`, `app/Exceptions/`

## 6. Multi-tenancy Pattern

Models use `BelongsToParish` trait → auto-applies `ParishScope` global scope.
Every action/DTO receives `parish_id` explicitly — never read from `auth()` at domain layer.

```php
class Encounter extends Model {
    use BelongsToParish, HasUuid, SoftDeletes;
}
```
Ref: `app/Support/Traits/BelongsToParish.php`, `app/Infrastructure/Scopes/ParishScope.php`

## 7. Cache Key Pattern

All cache keys via `App\Support\CacheKey` static methods. Never inline strings.

```php
Cache::forget(CacheKey::teamSuggestions($team->id));
Cache::remember(CacheKey::narrativeBase($encounterId), 3600, fn() => ...);
```
Ref: `app/Support/CacheKey.php`

## 8. Eloquent Model Pattern

Models use `HasUuid` (UUID primary key), `BelongsToParish` (tenant scope), `SoftDeletes`. Casts array defined via `casts(): array` method. Factory via `newFactory()`.

```php
class Encounter extends Model {
    use BelongsToParish, HasFactory, HasUuid, SoftDeletes;
    protected function casts(): array { return ['status' => EncounterStatus::class]; }
}
```
Ref: `app/Domain/Encounter/Models/Encounter.php`

## 9. FormRequest Pattern

Extends `FormRequest`. `authorize()` checks role via `hasAnyRole()`. `rules()` returns validation array.

```php
class StoreEncounterRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->hasAnyRole([...]); }
    public function rules(): array { return ['movement_id' => ['required', 'uuid', 'exists:movements,id'], ...]; }
}
```
Ref: `app/Http/Requests/Encounter/StoreEncounterRequest.php`

## 10. Resource Pattern

Extends `JsonResource`. Returns flat array. Uses `whenLoaded()` for conditional relationships. Enum values via `->value`, labels via `->label()`.

```php
class EncounterResource extends JsonResource {
    public function toArray(Request $request): array {
        return ['status' => $this->status->value, 'movement' => $this->whenLoaded('movement', fn() => [...])];
    }
}
```
Ref: `app/Http/Resources/Encounter/EncounterResource.php`

## 11. AI Integration Pattern

`ClaudeService::complete()` or `completeAsJson()` for structured output. AI calls dispatched via queued Jobs. Prompt classes in `Domain/AI/Prompts/`. All calls auto-logged to `AiApiLog`.

Ref: `app/Domain/AI/Services/ClaudeService.php`, `app/Jobs/GenerateEncounterAnalysis.php`

## 12. Audit Pattern

`AuditLogger` injected into controller. Called after significant state changes (e.g., `encounter.completed`). Never injected into Actions.

```php
$audit->log('encounter.completed', "Encounter completed.", $encounter, ['edition' => $encounter->edition_number]);
```
Ref: `app/Domain/Audit/AuditLogger.php`
