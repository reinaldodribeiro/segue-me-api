<?php

namespace App\Http\Controllers\Api\People;

use App\Domain\Audit\AuditLogger;
use App\Domain\People\Actions\CreatePerson;
use App\Domain\People\Actions\DetectDuplicates;
use App\Domain\People\Actions\UpdatePerson;
use App\Domain\People\DTOs\CreatePersonDTO;
use App\Domain\People\DTOs\UpdatePersonDTO;
use App\Domain\People\Models\Person;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use App\Domain\People\Services\EngagementScoreCalculator;
use App\Exports\PeopleExport;
use App\Exports\PeopleImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\StorePersonRequest;
use App\Http\Requests\People\UpdatePersonRequest;
use App\Http\Resources\People\PersonHistoryResource;
use App\Http\Resources\People\PersonResource;
use App\Jobs\ProcessFichaOcr;
use App\Jobs\ProcessSpreadsheetImport;
use App\Support\CacheKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PersonController extends Controller
{
    public function __construct(
        private readonly PersonRepositoryInterface $people,
        private readonly EngagementScoreCalculator $calculator,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Person::class);

        $filters = $request->only(['search', 'type', 'skills', 'diocese_id', 'sector_id', 'parish_id', 'sort_by', 'sort_dir', 'encounter_year']);

        return PersonResource::collection(
            $this->people->paginate($filters, $request->integer('per_page', 30))
        );
    }

    public function store(StorePersonRequest $request, CreatePerson $action, DetectDuplicates $detect): JsonResponse
    {
        $this->authorize('create', Person::class);

        // Verifica duplicatas a menos que o usuário confirme com force=true
        if (! $request->boolean('force')) {
            $parishId = $request->user()->parish_id;
            $duplicates = $detect->execute(
                $request->input('name'),
                $request->input('phones.0'),
                $request->input('email'),
                $parishId,
            );

            if ($duplicates->isNotEmpty()) {
                return response()->json([
                    'message' => 'Possível duplicata detectada. Envie com force=true para confirmar o cadastro.',
                    'duplicates' => $duplicates->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                    ]),
                ], 409);
            }
        }

        $person = $action->execute(CreatePersonDTO::fromRequest($request));

        return PersonResource::make($person)
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): PersonResource
    {
        $person = $this->people->findOrFail($id);
        $this->authorize('view', $person);

        $person->load([
            'parish',
            'teamMembers.team.encounter.movement',
            'teamMembers.memberEvaluation',
            'teamExperiences.movementTeam',
        ]);

        // Sort history by invited_at desc
        $person->setRelation(
            'teamMembers',
            $person->teamMembers->sortByDesc('invited_at')->values()
        );

        return PersonResource::make($person);
    }

    public function update(UpdatePersonRequest $request, string $id, UpdatePerson $action): PersonResource
    {
        $person = $this->people->findOrFail($id);
        $this->authorize('update', $person);

        $updated = $action->execute($person, UpdatePersonDTO::fromRequest($request));

        return PersonResource::make($updated);
    }

    public function destroy(string $id, AuditLogger $audit): JsonResponse
    {
        $person = $this->people->findOrFail($id);
        $this->authorize('delete', $person);

        $audit->log(
            'person.deleted',
            "Pessoa \"{$person->name}\" removida.",
            $person,
            ['name' => $person->name, 'email' => $person->email]
        );

        $this->people->delete($person);

        return response()->json(['message' => 'Pessoa removida com sucesso.']);
    }

    public function history(string $id): AnonymousResourceCollection
    {
        $person = $this->people->findOrFail($id);

        $history = $person->teamMembers()
            ->with(['team', 'team.encounter', 'team.encounter.movement', 'memberEvaluation'])
            ->latest('invited_at')
            ->get();

        return PersonHistoryResource::collection($history);
    }

    public function importSpreadsheet(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', File::default()->extensions(['xlsx', 'xls', 'csv'])->max(5 * 1024)],
            'parish_id' => ['sometimes', 'required', 'string', 'exists:parishes,id'],
        ]);

        $parishId = $request->input('parish_id', $request->user()->parish_id);

        if (! $parishId) {
            return response()->json(['message' => 'Informe a paróquia para importação.'], 422);
        }

        $path = $request->file('file')->store('imports/spreadsheets', 'local');
        $cacheKey = CacheKey::spreadsheetImport();

        ProcessSpreadsheetImport::dispatch(
            $path,
            $parishId,
            $cacheKey
        );

        return response()->json([
            'message' => 'Importação iniciada. Consulte o status com a chave fornecida.',
            'cache_key' => $cacheKey,
        ], 202);
    }

    public function importScan(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $path = $request->file('file')->store('imports/scans', 'local');
        $cacheKey = CacheKey::ocrImport();

        ProcessFichaOcr::dispatch(
            $path,
            $request->user()->parish_id,
            $request->user()->id,
            $cacheKey
        );

        return response()->json([
            'message' => 'Ficha enviada para processamento. Consulte o status com a chave fornecida.',
            'cache_key' => $cacheKey,
        ], 202);
    }

    public function importStatus(Request $request): JsonResponse
    {
        $request->validate(['cache_key' => ['required', 'string']]);

        $result = Cache::get($request->cache_key);

        if (! $result) {
            return response()->json(['status' => 'processing']);
        }

        return response()->json($result);
    }

    public function importTemplate(Request $request): BinaryFileResponse
    {
        $type = $request->query('type', 'youth');
        if (! in_array($type, ['youth', 'couple'])) {
            $type = 'youth';
        }
        $filename = $type === 'couple'
            ? 'modelo-importacao-casais.xlsx'
            : 'modelo-importacao-jovens.xlsx';

        return Excel::download(new PeopleImportTemplateExport($type), $filename);
    }

    public function recalculateScore(string $id): JsonResponse
    {
        $person = $this->people->findOrFail($id);
        $this->authorize('update', $person);
        $this->calculator->recalculateAndSave($person);

        return PersonResource::make($person->refresh())->response();
    }

    public function uploadPhoto(Request $request, string $id): JsonResponse
    {
        $person = $this->people->findOrFail($id);
        $this->authorize('update', $person);

        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        // Delete old photo
        if ($person->photo) {
            Storage::disk('public')->delete($person->photo);
        }

        $path = $request->file('photo')->store('people/photos', 'public');
        $this->people->update($person, ['photo' => $path]);

        return response()->json(['data' => ['photo' => $path]]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $parishId = $request->user()->parish_id;
        $filters = $request->only(['type', 'skills']);
        $filename = 'pessoas-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new PeopleExport($parishId, $filters), $filename);
    }
}
