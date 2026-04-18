<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Actions\CreateMovementTeam;
use App\Domain\Encounter\Actions\UpdateMovementTeam;
use App\Domain\Encounter\DTOs\CreateMovementTeamDTO;
use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\StoreMovementTeamRequest;
use App\Http\Resources\Encounter\MovementTeamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovementTeamController extends Controller
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function index(string $movementId): AnonymousResourceCollection
    {
        // Garante que o movimento pertence à paróquia do usuário
        $this->movements->findOrFail($movementId);

        return MovementTeamResource::collection(
            $this->movements->teamsOf($movementId)
        );
    }

    public function store(
        StoreMovementTeamRequest $request,
        string $movementId,
        CreateMovementTeam $action
    ): JsonResponse {
        $movement = $this->movements->findOrFail($movementId);
        $movementTeam = $action->execute(
            $movement,
            CreateMovementTeamDTO::fromRequest($request, $movementId)
        );

        return MovementTeamResource::make($movementTeam)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $movementId, string $teamId): MovementTeamResource
    {
        $this->movements->findOrFail($movementId);

        return MovementTeamResource::make(
            $this->movements->findTeamOrFail($teamId)
        );
    }

    public function update(
        StoreMovementTeamRequest $request,
        string $movementId,
        string $teamId,
        UpdateMovementTeam $action
    ): MovementTeamResource {
        $this->movements->findOrFail($movementId);
        $movementTeam = $this->movements->findTeamOrFail($teamId);

        $updated = $action->execute(
            $movementTeam,
            CreateMovementTeamDTO::fromRequest($request, $movementId)
        );

        return MovementTeamResource::make($updated);
    }

    public function destroy(string $movementId, string $teamId): JsonResponse
    {
        $this->movements->findOrFail($movementId);
        $movementTeam = $this->movements->findTeamOrFail($teamId);

        $this->movements->deleteTeam($movementTeam);

        return response()->json(['message' => 'Equipe padrão removida com sucesso.']);
    }

    public function reorder(string $movementId, Request $request): JsonResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['uuid', 'exists:movement_teams,id'],
        ]);

        $this->movements->findOrFail($movementId);

        foreach ($request->order as $position => $teamId) {
            $this->movements->findTeamOrFail($teamId)->update(['order' => $position]);
        }

        return response()->json(['message' => 'Ordem atualizada com sucesso.']);
    }
}
