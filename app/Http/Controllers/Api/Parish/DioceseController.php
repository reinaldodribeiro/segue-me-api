<?php

namespace App\Http\Controllers\Api\Parish;

use App\Domain\Parish\Actions\CreateDiocese;
use App\Domain\Parish\Actions\UpdateDiocese;
use App\Domain\Parish\DTOs\CreateDioceseDTO;
use App\Domain\Parish\DTOs\UpdateDioceseDTO;
use App\Domain\Parish\Repositories\DioceseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parish\StoreDioceseRequest;
use App\Http\Requests\Parish\UpdateDioceseRequest;
use App\Http\Resources\Parish\DioceseResource;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DioceseController extends Controller
{
    public function __construct(
        private readonly DioceseRepositoryInterface $dioceses,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return DioceseResource::collection(
            $this->dioceses->paginate()
        );
    }

    public function store(StoreDioceseRequest $request, CreateDiocese $action): JsonResponse
    {
        $diocese = $action->execute(CreateDioceseDTO::fromRequest($request));

        return DioceseResource::make($diocese)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): DioceseResource
    {
        return DioceseResource::make(
            $this->dioceses->findOrFail($id)
        );
    }

    public function update(UpdateDioceseRequest $request, string $id, UpdateDiocese $action): DioceseResource
    {
        $diocese = $this->dioceses->findOrFail($id);
        $updated = $action->execute($diocese, UpdateDioceseDTO::fromRequest($request));

        return DioceseResource::make($updated);
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdmin->value), 403, 'Acesso não autorizado.');
        $diocese = $this->dioceses->findOrFail($id);
        $this->dioceses->delete($diocese);

        return response()->json(['message' => 'Diocese removida com sucesso.']);
    }
}
