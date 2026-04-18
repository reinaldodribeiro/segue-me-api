<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Actions\CreateMovement;
use App\Domain\Encounter\Actions\UpdateMovement;
use App\Domain\Encounter\DTOs\CreateMovementDTO;
use App\Domain\Encounter\DTOs\UpdateMovementDTO;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\StoreMovementRequest;
use App\Http\Requests\Encounter\UpdateMovementRequest;
use App\Http\Resources\Encounter\MovementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovementController extends Controller
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Movement::class);

        $filters = [];
        $movementIds = $request->user()->accessibleMovementIds();
        if ($movementIds !== null) {
            $filters['movement_ids'] = $movementIds;
        }

        $movements = $this->movements->paginate($request->integer('per_page', 20), $filters);

        return MovementResource::collection($movements);
    }

    public function store(StoreMovementRequest $request, CreateMovement $action): JsonResponse
    {
        $this->authorize('create', Movement::class);

        $movement = $action->execute(CreateMovementDTO::fromRequest($request));

        return MovementResource::make($movement->load('movementTeams'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): MovementResource
    {
        $movement = $this->movements->findOrFail($id);
        $this->authorize('view', $movement);

        return MovementResource::make($movement->load('movementTeams'));
    }

    public function update(UpdateMovementRequest $request, string $id, UpdateMovement $action): MovementResource
    {
        $movement = $this->movements->findOrFail($id);
        $this->authorize('update', $movement);

        $updated = $action->execute($movement, UpdateMovementDTO::fromRequest($request));

        return MovementResource::make($updated->load('movementTeams'));
    }

    public function destroy(string $id): JsonResponse
    {
        $movement = $this->movements->findOrFail($id);
        $this->authorize('delete', $movement);

        // Impede remoção se houver encontros vinculados
        if ($movement->encounters()->exists()) {
            return response()->json([
                'message' => 'Não é possível remover um movimento que possui encontros vinculados. Desative-o em vez disso.',
            ], 422);
        }

        $this->movements->delete($movement);

        return response()->json(['message' => 'Movimento removido com sucesso.']);
    }
}
