<?php

namespace App\Http\Controllers\Api\Parish;

use App\Domain\Parish\Actions\CreateSector;
use App\Domain\Parish\DTOs\CreateSectorDTO;
use App\Domain\Parish\Repositories\SectorRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parish\StoreSectorRequest;
use App\Http\Requests\Parish\UpdateSectorRequest;
use App\Http\Resources\Parish\SectorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SectorController extends Controller
{
    public function __construct(
        private readonly SectorRepositoryInterface $sectors,
    ) {}

    public function index(?string $dioceseId = null): AnonymousResourceCollection
    {
        $perPage = request()->integer('per_page', 20);

        if ($dioceseId) {
            return SectorResource::collection(
                $this->sectors->paginateByDiocese($dioceseId, $perPage)
            );
        }

        $filters = array_filter([
            'name' => request('name'),
            'diocese_id' => request('diocese_id'),
            'active' => request('active', ''),
        ], fn ($v) => $v !== null && $v !== '');

        return SectorResource::collection(
            $this->sectors->paginate($perPage, $filters)
        );
    }

    public function store(StoreSectorRequest $request, string $dioceseId, CreateSector $action): JsonResponse
    {
        $sector = $action->execute(CreateSectorDTO::fromRequest($request, $dioceseId));

        return SectorResource::make($sector)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): SectorResource
    {
        return SectorResource::make(
            $this->sectors->findOrFail($id)->load('diocese')
        );
    }

    public function update(UpdateSectorRequest $request, string $id): SectorResource
    {
        $sector = $this->sectors->findOrFail($id);
        $this->sectors->update($sector, $request->validated());

        return SectorResource::make($sector->refresh()->load('diocese'));
    }

    public function destroy(string $id): JsonResponse
    {
        $sector = $this->sectors->findOrFail($id);
        $this->sectors->delete($sector);

        return response()->json(['message' => 'Setor removido com sucesso.']);
    }
}
