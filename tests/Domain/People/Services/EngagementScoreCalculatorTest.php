<?php

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Parish\Models\Parish;
use App\Domain\People\Models\Person;
use App\Domain\People\Services\EngagementScoreCalculator;
use App\Support\Enums\TeamMemberStatus;

beforeEach(function () {
    $this->parish = Parish::factory()->create();
    $this->person = Person::factory()->create(['parish_id' => $this->parish->id]);
    $this->calculator = app(EngagementScoreCalculator::class);
});

it('returns zero for a person with no activity', function () {
    expect($this->calculator->calculate($this->person))->toBe(0);
});

it('adds points for confirmed participations', function () {
    $encounter = Encounter::factory()->create(['parish_id' => $this->parish->id]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id]);

    TeamMember::factory()->create([
        'person_id' => $this->person->id,
        'team_id' => $team->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    // 1 confirmed × 10 + 1 distinct × 5 = 15
    expect($this->calculator->calculate($this->person))->toBe(15);
});

it('deducts points for refusals', function () {
    $encounter = Encounter::factory()->create(['parish_id' => $this->parish->id]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id]);

    TeamMember::factory()->create([
        'person_id' => $this->person->id,
        'team_id' => $team->id,
        'status' => TeamMemberStatus::Refused,
    ]);

    // 0 confirmed, 0 distinct (refused não conta), 1 refused × 3 = -3 → clamped to 0
    expect($this->calculator->calculate($this->person))->toBe(0);
});

it('score never goes below zero', function () {
    // Cada recusa precisa ser em um encontro/equipe diferente (constraint única por team+person)
    collect(range(1, 5))->each(function () {
        $encounter = Encounter::factory()->create(['parish_id' => $this->parish->id]);
        $team = Team::factory()->create(['encounter_id' => $encounter->id]);

        TeamMember::factory()->create([
            'person_id' => $this->person->id,
            'team_id' => $team->id,
            'status' => TeamMemberStatus::Refused,
        ]);
    });

    expect($this->calculator->calculate($this->person))->toBe(0);
});

it('caps years active bonus at 20 points', function () {
    // Simula pessoa com 20 anos de atividade (born 1990, first encounter 2005)
    $person = Person::factory()->create([
        'parish_id' => $this->parish->id,
        'birth_date' => '1990-01-01',
        'created_at' => now()->subYears(15),
    ]);

    // Anos × 2 = 30, mas cap é 20
    $score = $this->calculator->calculate($person);

    expect($score)->toBeLessThanOrEqual(20);
});

it('recalculates and persists score to database', function () {
    $encounter = Encounter::factory()->create(['parish_id' => $this->parish->id]);
    $team = Team::factory()->create(['encounter_id' => $encounter->id]);

    TeamMember::factory()->create([
        'person_id' => $this->person->id,
        'team_id' => $team->id,
        'status' => TeamMemberStatus::Confirmed,
    ]);

    $this->calculator->recalculateAndSave($this->person);

    expect($this->person->fresh()->engagement_score)->toBeGreaterThan(0);
});
