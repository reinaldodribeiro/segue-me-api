<?php

namespace App\Domain\People\Services;

use App\Domain\People\Models\Person;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EngagementScoreCalculator
{
    private const CONFIRMED_WEIGHT = 10;

    private const DISTINCT_TEAM_BONUS = 5;

    private const REFUSAL_PENALTY = 3;

    private const YEARS_WEIGHT = 2;

    private const YEARS_CAP = 20;

    private const EXP_MEMBER_WEIGHT = 10;

    private const EXP_COORDINATOR_WEIGHT = 10;

    /**
     * Calculate score for a single person, issuing one aggregate query
     * for team_members metrics and loading teamExperiences only if not
     * already present on the model.
     */
    public function calculate(Person $person): int
    {
        $metrics = $this->fetchMetrics($person->id);

        $person->loadMissing('teamExperiences');
        $experiences = $person->teamExperiences;

        $expMembers = $experiences->where('role', 'member')->count();
        $expCoordinators = $experiences->where('role', 'coordinator')->count();

        return $this->computeScore(
            confirmed: (int) $metrics->confirmed,
            distinct: (int) $metrics->distinct_teams,
            refused: (int) $metrics->refused,
            years: $this->yearsFromDate($metrics->first_invited_at),
            expMembers: $expMembers,
            expCoordinators: $expCoordinators,
        );
    }

    public function recalculateAndSave(Person $person): void
    {
        $person->update(['engagement_score' => $this->calculate($person)]);
    }

    /**
     * Batch-calculate and persist scores for multiple person IDs using
     * a single aggregate query per metric set. Designed for bulk
     * operations (e.g. encounter completed, spreadsheet import).
     *
     * @param  array<string>  $personIds
     */
    public function recalculateBatch(array $personIds): void
    {
        if (empty($personIds)) {
            return;
        }

        // One aggregate query for all team_member metrics across the batch.
        $metricsMap = DB::table('team_members')
            ->select([
                'person_id',
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("COUNT(DISTINCT CASE WHEN status = 'confirmed' THEN team_id END) as distinct_teams"),
                DB::raw("SUM(CASE WHEN status = 'refused' THEN 1 ELSE 0 END) as refused"),
                DB::raw('MIN(invited_at) as first_invited_at'),
            ])
            ->whereIn('person_id', $personIds)
            ->groupBy('person_id')
            ->get()
            ->keyBy('person_id');

        // One query to load all team experiences across the batch.
        $experiencesMap = DB::table('person_team_experiences')
            ->select(['person_id', 'role'])
            ->whereIn('person_id', $personIds)
            ->get()
            ->groupBy('person_id');

        $updates = [];

        foreach ($personIds as $personId) {
            $m = $metricsMap->get($personId);
            $experiences = $experiencesMap->get($personId, collect());

            $expMembers = $experiences->where('role', 'member')->count();
            $expCoordinators = $experiences->where('role', 'coordinator')->count();

            $score = $this->computeScore(
                confirmed: $m ? (int) $m->confirmed : 0,
                distinct: $m ? (int) $m->distinct_teams : 0,
                refused: $m ? (int) $m->refused : 0,
                years: $m ? $this->yearsFromDate($m->first_invited_at) : 0,
                expMembers: $expMembers,
                expCoordinators: $expCoordinators,
            );

            $updates[$personId] = $score;
        }

        // Persist in chunks to avoid hitting SQLite / MySQL parameter limits.
        foreach (array_chunk($updates, 500, true) as $chunk) {
            foreach ($chunk as $personId => $score) {
                Person::where('id', $personId)->update(['engagement_score' => $score]);
            }
        }
    }

    /**
     * Core formula — pure calculation, no I/O.
     */
    private function computeScore(
        int $confirmed,
        int $distinct,
        int $refused,
        int $years,
        int $expMembers,
        int $expCoordinators,
    ): int {
        $score = ($confirmed * self::CONFIRMED_WEIGHT)
            + ($distinct * self::DISTINCT_TEAM_BONUS)
            - ($refused * self::REFUSAL_PENALTY)
            + min($years * self::YEARS_WEIGHT, self::YEARS_CAP)
            + ($expMembers * self::EXP_MEMBER_WEIGHT)
            + ($expCoordinators * self::EXP_COORDINATOR_WEIGHT);

        return max(0, $score);
    }

    /**
     * Fetch all team_member aggregate metrics for a single person in one query.
     */
    private function fetchMetrics(string $personId): object
    {
        return DB::table('team_members')
            ->select([
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("COUNT(DISTINCT CASE WHEN status = 'confirmed' THEN team_id END) as distinct_teams"),
                DB::raw("SUM(CASE WHEN status = 'refused' THEN 1 ELSE 0 END) as refused"),
                DB::raw('MIN(invited_at) as first_invited_at'),
            ])
            ->where('person_id', $personId)
            ->first() ?? (object) ['confirmed' => 0, 'distinct_teams' => 0, 'refused' => 0, 'first_invited_at' => null];
    }

    private function yearsFromDate(?string $date): int
    {
        if (! $date) {
            return 0;
        }

        return (int) now()->diffInYears(Carbon::parse($date));
    }
}
