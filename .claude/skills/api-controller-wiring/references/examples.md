<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Controller Wiring — Examples

## Full CRUD controller (Encounter)

```php
class EncounterController extends Controller {
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
        private readonly PersonRepositoryInterface $people,
    ) {}

    public function index(Request $request): AnonymousResourceCollection {
        $this->authorize('viewAny', Encounter::class);
        $filters = $request->only(['status', 'movement_id']);
        return EncounterResource::collection($this->encounters->paginate($filters));
    }

    public function store(StoreEncounterRequest $request, CreateEncounter $action): JsonResponse {
        $this->authorize('create', Encounter::class);
        $encounter = $action->execute(CreateEncounterDTO::fromRequest($request));
        return EncounterResource::make($encounter)->response()->setStatusCode(201);
    }

    public function destroy(string $id): JsonResponse {
        $encounter = $this->encounters->findOrFail($id);
        $this->authorize('delete', $encounter);
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);
        $this->encounters->delete($encounter);
        return response()->json(['message' => 'Encontro removido com sucesso.']);
    }
}
```
Ref: `app/Http/Controllers/Api/Encounter/EncounterController.php`

## Route registration pattern

```php
// routes/api.php — inside auth:sanctum middleware group
Route::apiResource('encounters', EncounterController::class);
Route::get('encounters/{encounter}/summary', [EncounterController::class, 'summary'])->name('encounters.summary');
Route::delete('encounters/{encounter}/members', [EncounterController::class, 'resetMembers'])->name('encounters.reset-members');
```
Ref: `routes/api.php`
