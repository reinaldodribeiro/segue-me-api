<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Actions\StoreEncounterParticipant;
use App\Domain\Encounter\Actions\UpdateEncounterParticipantPhoto;
use App\Domain\Encounter\Repositories\EncounterParticipantRepositoryInterface;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Exports\EncounterParticipantsExport;
use App\Exports\EncounterParticipantsTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\StoreEncounterParticipantRequest;
use App\Http\Requests\Encounter\UpdateEncounterParticipantRequest;
use App\Http\Resources\Encounter\EncounterParticipantResource;
use App\Imports\EncounterParticipantsImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EncounterParticipantController extends Controller
{
    public function __construct(
        private readonly EncounterParticipantRepositoryInterface $participants,
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function index(string $encounterId): AnonymousResourceCollection
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $participants = $this->participants->findByEncounter($encounterId);

        return EncounterParticipantResource::collection($participants);
    }

    public function store(StoreEncounterParticipantRequest $request, string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        if ($encounter->max_participants !== null) {
            $current = $this->participants->countByEncounter($encounterId);
            if ($current >= $encounter->max_participants) {
                return response()->json([
                    'message' => "Limite máximo de {$encounter->max_participants} encontristas atingido.",
                ], 422);
            }
        }

        $participant = app(StoreEncounterParticipant::class)->execute(
            $encounter,
            $request->validated(),
        );

        return EncounterParticipantResource::make($participant)
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEncounterParticipantRequest $request, string $encounterId, string $participantId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        $participant = $this->participants->findOrFail($encounterId, $participantId);

        $updated = $this->participants->update($participant, $request->validated());

        return EncounterParticipantResource::make($updated)->response();
    }

    public function destroy(string $encounterId, string $participantId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        $participant = $this->participants->findOrFail($encounterId, $participantId);

        $this->participants->delete($participant);

        return response()->json(['message' => 'Encontrista removido com sucesso.']);
    }

    public function uploadPhoto(Request $request, string $encounterId, string $participantId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        $participant = $this->participants->findOrFail($encounterId, $participantId);

        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $updated = app(UpdateEncounterParticipantPhoto::class)->execute(
            $participant,
            $request->file('photo'),
        );

        return EncounterParticipantResource::make($updated)->response();
    }

    public function importTemplate(): BinaryFileResponse
    {
        return Excel::download(new EncounterParticipantsTemplateExport, 'modelo-encontristas.xlsx');
    }

    public function import(Request $request, string $encounterId): JsonResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        $request->validate([
            'file' => ['required', File::default()->extensions(['xlsx', 'xls', 'csv'])->max(5 * 1024)],
        ]);

        $import = new EncounterParticipantsImport($encounterId);
        Excel::import($import, $request->file('file'));

        $errors = $import->errors();

        return response()->json([
            'message' => "Importação concluída. {$import->getImportedCount()} encontristas importados.",
            'imported' => $import->getImportedCount(),
            'errors' => $errors->map(fn ($e) => $e->getMessage())->values(),
        ]);
    }

    public function exportExcel(string $encounterId): BinaryFileResponse
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $filename = 'encontristas-'.($encounter->edition_number ?? $encounterId).'.xlsx';

        return Excel::download(new EncounterParticipantsExport($encounterId), $filename);
    }

    public function exportPdf(string $encounterId): Response
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('view', $encounter);

        $participants = $this->participants->findByEncounter($encounterId);

        $parish = $encounter->parish()->first();

        $pdf = Pdf::loadView('reports.encounter-participants', [
            'encounter' => $encounter,
            'participants' => $participants,
            'parish' => $parish,
        ]);

        $filename = 'encontristas-'.($encounter->edition_number ?? $encounterId).'.pdf';

        return $pdf->download($filename);
    }
}
