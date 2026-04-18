<?php

namespace App\Domain\People\Services;

use App\Domain\People\Models\Person;

class EngagementScoreCalculator
{
    private const CONFIRMED_WEIGHT = 10;

    private const DISTINCT_TEAM_BONUS = 5;

    private const REFUSAL_PENALTY = 3;

    private const YEARS_WEIGHT = 2;

    private const YEARS_CAP = 20;

    private const EXP_MEMBER_WEIGHT = 10;

    private const EXP_COORDINATOR_WEIGHT = 10;

    public function calculate(Person $person): int
    {
        $confirmed = $person->teamMembers()->confirmed()->count();
        $distinct = $person->distinctTeamsCount();
        $refused = $person->teamMembers()->refused()->count();
        $years = $person->yearsActive();

        $experiences = $person->teamExperiences;
        $expMembers = $experiences->where('role', 'member')->count();
        $expCoordinators = $experiences->where('role', 'coordinator')->count();

        $score = ($confirmed * self::CONFIRMED_WEIGHT)
            + ($distinct * self::DISTINCT_TEAM_BONUS)
            - ($refused * self::REFUSAL_PENALTY)
            + min($years * self::YEARS_WEIGHT, self::YEARS_CAP)
            + ($expMembers * self::EXP_MEMBER_WEIGHT)
            + ($expCoordinators * self::EXP_COORDINATOR_WEIGHT);

        return max(0, $score);
    }

    public function recalculateAndSave(Person $person): void
    {
        $person->update(['engagement_score' => $this->calculate($person)]);
    }
}
