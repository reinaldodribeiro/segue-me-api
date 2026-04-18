<?php

namespace App\Http\Controllers\Api\Parish;

use App\Domain\Parish\Actions\CreateParish;
use App\Domain\Parish\Actions\UploadParishLogo;
use App\Domain\Parish\DTOs\CreateParishDTO;
use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parish\StoreParishRequest;
use App\Http\Requests\Parish\UpdateParishRequest;
use App\Http\Resources\Parish\ParishResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParishController extends Controller
{
    public function __construct(
        private readonly ParishRepositoryInterface $parishes,
    ) {}

    public function index(?string $sectorId = null): AnonymousResourceCollection
    {
        $perPage = request()->integer('per_page', 20);

        if ($sectorId) {
            return ParishResource::collection(
                $this->parishes->paginateBySector($sectorId, $perPage)
            );
        }

        $filters = array_filter([
            'name' => request('name'),
            'sector_id' => request('sector_id'),
            'diocese_id' => request('diocese_id'),
            'active' => request('active', ''),
        ], fn ($v) => $v !== null && $v !== '');

        return ParishResource::collection(
            $this->parishes->paginate($perPage, $filters)
        );
    }

    public function store(StoreParishRequest $request, string $sectorId, CreateParish $action): JsonResponse
    {
        $parish = $action->execute(CreateParishDTO::fromRequest($request, $sectorId));

        return ParishResource::make($parish)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): ParishResource
    {
        return ParishResource::make(
            $this->parishes->findOrFail($id)->load('sector')
        );
    }

    public function update(UpdateParishRequest $request, string $id): ParishResource
    {
        $parish = $this->parishes->findOrFail($id);
        $this->parishes->update($parish, $request->validated());

        return ParishResource::make($parish->refresh()->load('sector'));
    }

    public function destroy(string $id): JsonResponse
    {
        $parish = $this->parishes->findOrFail($id);
        $this->parishes->delete($parish);

        return response()->json(['message' => 'Paróquia removida com sucesso.']);
    }

    public function uploadLogo(Request $request, string $id, UploadParishLogo $action): ParishResource
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $parish = $this->parishes->findOrFail($id);

        return ParishResource::make(
            $action->execute($parish, $request->file('logo'))
        );
    }
}
