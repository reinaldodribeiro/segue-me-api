<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class EncounterReportController extends Controller
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function pdf(string $id): Response
    {
        $encounter = $this->encounters->findOrFail($id);
        $encounter->load(['movement', 'teams.members.person', 'teams.members', 'responsibleUser']);
        $parish = $encounter->parish()->first();

        $pdf = Pdf::loadView('reports.encounter', [
            'encounter' => $encounter,
            'parish' => $parish,
        ]);

        return $pdf->download("encontro-{$encounter->edition_number}.pdf");
    }
}
