<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Models\MemberEvaluation;
use App\Domain\Encounter\Models\TeamEvaluation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Encounter\SubmitEvaluationRequest;
use App\Http\Requests\Encounter\VerifyEvaluationRequest;
use App\Http\Resources\Encounter\EvaluationFormResource;
use App\Support\Enums\EvaluationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class EvaluationPublicController extends Controller
{
    /**
     * Verify PIN and return form data + session token.
     */
    public function verify(VerifyEvaluationRequest $request, string $token): JsonResponse
    {
        $evaluation = TeamEvaluation::where('token', $token)->first();

        if (! $evaluation) {
            return response()->json(['message' => 'Link de avaliação inválido.'], 404);
        }

        if ($evaluation->isExpired()) {
            return response()->json(['message' => 'Este link de avaliação expirou.'], 410);
        }

        // Rate limit: lock after 5 failed attempts
        $lockKey = "evaluation_pin_lock:{$token}";
        $attemptsKey = "evaluation_pin_attempts:{$token}";

        if (Cache::has($lockKey)) {
            return response()->json([
                'message' => 'Muitas tentativas incorretas. Tente novamente em 15 minutos.',
            ], 429);
        }

        if ($evaluation->pin !== $request->pin) {
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(15));

            if ($attempts >= 5) {
                Cache::put($lockKey, true, now()->addMinutes(15));
            }

            return response()->json(['message' => 'PIN incorreto.'], 403);
        }

        // Clear attempts on success
        Cache::forget($attemptsKey);

        // Generate session token (encrypted, valid for 2 hours)
        $sessionToken = Crypt::encrypt([
            'token' => $token,
            'expires_at' => now()->addHours(2)->timestamp,
        ]);

        $evaluation->load(['team.members.person', 'encounter.movement']);

        return response()->json([
            'data' => [
                'session_token' => $sessionToken,
                'form' => new EvaluationFormResource($evaluation),
            ],
        ]);
    }

    /**
     * Submit completed evaluation.
     */
    public function submit(SubmitEvaluationRequest $request, string $token): JsonResponse
    {
        $evaluation = TeamEvaluation::where('token', $token)->first();

        if (! $evaluation) {
            return response()->json(['message' => 'Link de avaliação inválido.'], 404);
        }

        if ($evaluation->isSubmitted()) {
            return response()->json(['message' => 'Esta avaliação já foi submetida.'], 409);
        }

        if ($evaluation->isExpired()) {
            return response()->json(['message' => 'Este link de avaliação expirou.'], 410);
        }

        // Validate session token
        try {
            $session = Crypt::decrypt($request->session_token);
            if ($session['token'] !== $token || $session['expires_at'] < now()->timestamp) {
                return response()->json(['message' => 'Sessão expirada. Verifique o PIN novamente.'], 401);
            }
        } catch (\Exception) {
            return response()->json(['message' => 'Sessão inválida. Verifique o PIN novamente.'], 401);
        }

        // Save general team evaluation
        $evaluation->update([
            'preparation_rating' => $request->preparation_rating,
            'preparation_comment' => $request->preparation_comment,
            'teamwork_rating' => $request->teamwork_rating,
            'teamwork_comment' => $request->teamwork_comment,
            'materials_rating' => $request->materials_rating,
            'materials_comment' => $request->materials_comment,
            'issues_text' => $request->issues_text,
            'improvements_text' => $request->improvements_text,
            'overall_team_rating' => $request->overall_team_rating,
            'status' => EvaluationStatus::Submitted,
            'submitted_at' => now(),
        ]);

        // Save individual member evaluations
        foreach ($request->members as $memberData) {
            $teamMember = $evaluation->team->members()->findOrFail($memberData['team_member_id']);

            MemberEvaluation::create([
                'team_evaluation_id' => $evaluation->id,
                'team_member_id' => $memberData['team_member_id'],
                'person_id' => $teamMember->person_id,
                'commitment_rating' => $memberData['commitment_rating'],
                'fulfilled_responsibilities' => $memberData['fulfilled_responsibilities'],
                'positive_highlight' => $memberData['positive_highlight'] ?? null,
                'issue_observed' => $memberData['issue_observed'] ?? null,
                'recommend' => $memberData['recommend'],
            ]);
        }

        return response()->json(['message' => 'Avaliação submetida com sucesso!']);
    }
}
