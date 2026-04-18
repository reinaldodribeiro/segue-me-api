---
name: api-controller-wiring
description: "Pattern for thin Laravel controllers that wire HTTP to domain actions in the segue-me API.
  Controllers inject repositories in constructor, inject actions in method params, call authorize(), build DTO, call action, return Resource.
  Use when adding a new endpoint, creating a controller, wiring a route to an action, or the user says 'add endpoint', 'new route', 'expose via API', 'create controller method'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Controller Wiring Pattern

Thin controllers: constructor injects repositories, method params inject actions. Always: authorize → resolve entity → build DTO → execute action → return Resource. Return 201 with `->response()->setStatusCode(201)` for create.

## Pattern

- Namespace: `App\Http\Controllers\Api\{Domain}\{Entity}Controller`
- Constructor: inject only repository interfaces
- Method params: inject `FormRequest` + `Action` classes
- Step order: `$this->authorize()` → `$repo->findOrFail($id)` → `DTO::fromRequest()` → `$action->execute()` → `Resource::make()`
- `index`: return `Resource::collection($repo->paginate($filters))`
- `store`: return `Resource::make()->response()->setStatusCode(201)`
- `show`: load relations explicitly with `->load([])`
- `destroy`: return `response()->json(['message' => '...'])`
- `AuditLogger` injected as method param when needed — not in constructor

## Example

```php
class EncounterController extends Controller {
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function store(StoreEncounterRequest $request, CreateEncounter $action): JsonResponse {
        $this->authorize('create', Encounter::class);
        $encounter = $action->execute(CreateEncounterDTO::fromRequest($request));
        return EncounterResource::make($encounter)->response()->setStatusCode(201);
    }

    public function update(UpdateEncounterRequest $request, string $id, UpdateEncounter $action, AuditLogger $audit): EncounterResource {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('update', $encounter);
        $updated = $action->execute($encounter, UpdateEncounterDTO::fromRequest($request));
        return EncounterResource::make($updated);
    }
}
```
Ref: `app/Http/Controllers/Api/Encounter/EncounterController.php`

## References

For full examples with variants:
→ Read `references/examples.md`
