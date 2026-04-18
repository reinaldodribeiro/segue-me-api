<?php

namespace Database\Factories;

use App\Domain\Encounter\Models\Team;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\People\Models\Person;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'person_id' => Person::factory(),
            'replaced_by_id' => null,
            'status' => TeamMemberStatus::Pending,
            'refusal_reason' => null,
            'invited_at' => now(),
            'responded_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attr) => [
            'status' => TeamMemberStatus::Confirmed,
            'responded_at' => now(),
        ]);
    }

    public function refused(?string $reason = null): static
    {
        return $this->state(fn (array $attr) => [
            'status' => TeamMemberStatus::Refused,
            'refusal_reason' => $reason ?? 'Motivo não informado.',
            'responded_at' => now(),
        ]);
    }
}
