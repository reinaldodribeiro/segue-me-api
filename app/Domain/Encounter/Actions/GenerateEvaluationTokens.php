<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\TeamEvaluation;
use App\Support\Enums\EvaluationStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GenerateEvaluationTokens
{
    public function execute(Encounter $encounter): Collection
    {
        $encounter->load('teams.members');

        $evaluations = collect();

        foreach ($encounter->teams as $team) {
            // Only create for teams with at least 1 confirmed member
            if ($team->confirmedMembers()->count() === 0) {
                continue;
            }

            // Skip if evaluation already exists for this team
            if ($team->evaluation()->exists()) {
                $evaluations->push($team->evaluation);

                continue;
            }

            $evaluation = TeamEvaluation::create([
                'team_id' => $team->id,
                'encounter_id' => $encounter->id,
                'token' => Str::uuid()->toString(),
                'pin' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                'status' => EvaluationStatus::Pending,
                'expires_at' => now()->addDays(30),
            ]);

            $evaluations->push($evaluation);
        }

        return $evaluations;
    }
}
