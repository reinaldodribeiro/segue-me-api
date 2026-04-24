<?php

use App\Http\Controllers\Api\AI\AiApiLogController;
use App\Http\Controllers\Api\Audit\AuditLogController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RefreshController;
use App\Http\Controllers\Api\Auth\TutorialController;
use App\Http\Controllers\Api\Auth\UpdatePasswordController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Encounter\EncounterAnalysisController;
use App\Http\Controllers\Api\Encounter\EncounterController;
use App\Http\Controllers\Api\Encounter\EncounterParticipantController;
use App\Http\Controllers\Api\Encounter\EncounterReportController;
use App\Http\Controllers\Api\Encounter\EvaluationPublicController;
use App\Http\Controllers\Api\Encounter\EvaluationTokenController;
use App\Http\Controllers\Api\Encounter\MovementController;
use App\Http\Controllers\Api\Encounter\MovementTeamController;
use App\Http\Controllers\Api\Encounter\RefusalReportController;
use App\Http\Controllers\Api\Encounter\SyncTeamTemplatesController;
use App\Http\Controllers\Api\Encounter\TeamController;
use App\Http\Controllers\Api\Encounter\TeamMemberController;
use App\Http\Controllers\Api\JobStatusController;
use App\Http\Controllers\Api\Parish\DioceseController;
use App\Http\Controllers\Api\Parish\ParishController;
use App\Http\Controllers\Api\Parish\ParishReportController;
use App\Http\Controllers\Api\Parish\ParishSkillController;
use App\Http\Controllers\Api\Parish\SectorController;
use App\Http\Controllers\Api\People\PersonController;
use App\Http\Controllers\Api\People\PersonSuggestTeamsController;
use App\Http\Controllers\Api\People\PersonTeamExperienceController;
use Illuminate\Support\Facades\Route;

// Público
Route::post('auth/login', LoginController::class)->name('auth.login');
Route::get('people/import/template', [PersonController::class, 'importTemplate'])->name('people.import.template.public');
Route::get('encounters/participants/import/template', [EncounterParticipantController::class, 'importTemplate'])->name('encounters.participants.import.template.public');

// Avaliação pública (sem autenticação)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('avaliacao/{token}/verify', [EvaluationPublicController::class, 'verify'])->name('evaluation.verify');
    Route::post('avaliacao/{token}/submit', [EvaluationPublicController::class, 'submit'])->name('evaluation.submit');
});

// Autenticado
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', LogoutController::class)->name('auth.logout');
    Route::post('auth/refresh', RefreshController::class)->name('auth.refresh');
    Route::get('auth/me', MeController::class)->name('auth.me');
    Route::put('me/password', UpdatePasswordController::class)->name('me.password');
    Route::get('me/tutorial', [TutorialController::class, 'index'])->name('me.tutorial.index');
    Route::post('me/tutorial', [TutorialController::class, 'markSeen'])->name('me.tutorial.mark');
    Route::delete('me/tutorial', [TutorialController::class, 'reset'])->name('me.tutorial.reset');

    // Status de jobs assíncronos (polling)
    Route::get('jobs/status', JobStatusController::class)->name('jobs.status');

    // Usuários
    Route::apiResource('users', UserController::class);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::put('users/{user}/movements', [UserController::class, 'syncMovements'])->name('users.sync-movements');

    // Auditoria
    Route::get('audit-logs', AuditLogController::class)->name('audit-logs.index');

    // Logs de IA (super_admin only)
    Route::get('ai-logs', [AiApiLogController::class, 'index'])->name('ai-logs.index');
    Route::get('ai-logs/stats', [AiApiLogController::class, 'stats'])->name('ai-logs.stats');

    // Administração — Diocese / Setor / Paróquia
    Route::apiResource('dioceses', DioceseController::class);
    Route::get('sectors', [SectorController::class, 'index'])->name('sectors.index');
    Route::apiResource('dioceses.sectors', SectorController::class)->shallow();
    Route::get('parishes', [ParishController::class, 'index'])->name('parishes.index');
    Route::apiResource('sectors.parishes', ParishController::class)->shallow();
    Route::post('parishes/{parish}/logo', [ParishController::class, 'uploadLogo'])->name('parishes.logo');
    Route::get('parishes/{parish}/report/engagement', [ParishReportController::class, 'engagement'])->name('parishes.report.engagement');
    Route::get('parishes/{parish}/skills', [ParishSkillController::class, 'index'])->name('parishes.skills.index');
    Route::post('parishes/{parish}/skills', [ParishSkillController::class, 'store'])->name('parishes.skills.store');
    Route::delete('parishes/{parish}/skills', [ParishSkillController::class, 'destroy'])->name('parishes.skills.destroy');

    // Pessoas
    Route::apiResource('people', PersonController::class);
    Route::get('people/{person}/history', [PersonController::class, 'history'])->name('people.history');
    Route::post('people/{person}/recalculate-score', [PersonController::class, 'recalculateScore'])->name('people.recalculate-score');
    Route::get('people/{person}/suggested-teams', PersonSuggestTeamsController::class)->name('people.suggested-teams');
    Route::post('people/import/spreadsheet', [PersonController::class, 'importSpreadsheet'])->name('people.import.spreadsheet');
    Route::post('people/import/scan', [PersonController::class, 'importScan'])->name('people.import.scan');
    Route::get('people/import/status', [PersonController::class, 'importStatus'])->name('people.import.status');
    Route::get('people/export/excel', [PersonController::class, 'exportExcel'])->name('people.export.excel');
    Route::get('people/{person}/team-experiences', [PersonTeamExperienceController::class, 'index'])->name('people.team-experiences.index');
    Route::post('people/{person}/team-experiences', [PersonTeamExperienceController::class, 'store'])->name('people.team-experiences.store');
    Route::delete('people/{person}/team-experiences/{experience}', [PersonTeamExperienceController::class, 'destroy'])->name('people.team-experiences.destroy');
    Route::post('people/{person}/photo', [PersonController::class, 'uploadPhoto'])->name('people.photo');

    // Movimentos
    Route::apiResource('movements', MovementController::class);
    Route::get('movements/{movement}/teams', [MovementTeamController::class, 'index'])->name('movement-teams.index');
    Route::post('movements/{movement}/teams', [MovementTeamController::class, 'store'])->name('movement-teams.store');
    Route::post('movements/{movement}/teams/reorder', [MovementTeamController::class, 'reorder'])->name('movement-teams.reorder');
    Route::get('movements/{movement}/teams/{team}', [MovementTeamController::class, 'show'])->name('movement-teams.show');
    Route::put('movements/{movement}/teams/{team}', [MovementTeamController::class, 'update'])->name('movement-teams.update');
    Route::delete('movements/{movement}/teams/{team}', [MovementTeamController::class, 'destroy'])->name('movement-teams.destroy');

    // Encontros
    Route::apiResource('encounters', EncounterController::class);
    Route::get('encounters/{encounter}/summary', [EncounterController::class, 'summary'])->name('encounters.summary');
    Route::get('encounters/{encounter}/available-people', [EncounterController::class, 'availablePeople'])->name('encounters.available-people');
    Route::get('encounters/{encounter}/previous-participants', [EncounterController::class, 'previousParticipants'])->name('encounters.previous-participants');
    Route::delete('encounters/{encounter}/members', [EncounterController::class, 'resetMembers'])->name('encounters.reset-members');
    Route::post('encounters/{encounter}/sync-teams', SyncTeamTemplatesController::class)->name('encounters.sync-teams');

    // Relatórios de encontro
    Route::get('encounters/{encounter}/report/pdf', [EncounterReportController::class, 'pdf'])->name('encounters.report.pdf');
    Route::get('encounters/{encounter}/report/refusals', RefusalReportController::class)->name('encounters.report.refusals');

    // Avaliações (tokens/links para coordenadores)
    Route::get('encounters/{encounter}/evaluations', [EvaluationTokenController::class, 'index'])->name('encounters.evaluations.index');
    Route::post('encounters/{encounter}/evaluations/generate', [EvaluationTokenController::class, 'generate'])->name('encounters.evaluations.generate');
    Route::post('encounters/{encounter}/evaluations/{team}/regenerate', [EvaluationTokenController::class, 'regenerate'])->name('encounters.evaluations.regenerate');

    // Encontristas
    Route::get('encounters/{encounter}/participants', [EncounterParticipantController::class, 'index'])->name('encounters.participants.index');
    Route::post('encounters/{encounter}/participants', [EncounterParticipantController::class, 'store'])->name('encounters.participants.store');
    Route::patch('encounters/{encounter}/participants/{participant}', [EncounterParticipantController::class, 'update'])->name('encounters.participants.update');
    Route::delete('encounters/{encounter}/participants/{participant}', [EncounterParticipantController::class, 'destroy'])->name('encounters.participants.destroy');
    Route::post('encounters/{encounter}/participants/{participant}/photo', [EncounterParticipantController::class, 'uploadPhoto'])->name('encounters.participants.photo');
    Route::post('encounters/{encounter}/participants/import', [EncounterParticipantController::class, 'import'])->name('encounters.participants.import');
    Route::get('encounters/{encounter}/participants/export/excel', [EncounterParticipantController::class, 'exportExcel'])->name('encounters.participants.export.excel');
    Route::get('encounters/{encounter}/participants/export/pdf', [EncounterParticipantController::class, 'exportPdf'])->name('encounters.participants.export.pdf');

    // Análise do encontro (IA)
    Route::get('encounters/{encounter}/analysis', [EncounterAnalysisController::class, 'show'])->name('encounters.analysis.show');
    Route::post('encounters/{encounter}/analysis/generate', [EncounterAnalysisController::class, 'generate'])->name('encounters.analysis.generate');
    Route::get('encounters/{encounter}/analysis/progress', [EncounterAnalysisController::class, 'progress'])->name('encounters.analysis.progress');
    Route::get('encounters/{encounter}/analysis/pdf', [EncounterAnalysisController::class, 'pdf'])->name('encounters.analysis.pdf');

    // Equipes
    Route::apiResource('encounters.teams', TeamController::class)->shallow();

    // Membros
    Route::post('teams/{team}/members', [TeamMemberController::class, 'store'])->name('team-members.store');
    Route::delete('team-members/{teamMember}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');
    Route::patch('team-members/{teamMember}/status', [TeamMemberController::class, 'updateStatus'])->name('team-members.status');

    // Sugestões de IA (rate-limited: 3 req/min)
    Route::middleware('throttle:3,1')->group(function () {
        Route::get('teams/{team}/suggest-members', [TeamController::class, 'suggestMembers'])->name('teams.suggest-members');
        Route::get('team-members/{teamMember}/suggest-replacement', [TeamMemberController::class, 'suggestReplacement'])->name('team-members.suggest-replacement');
    });
});
