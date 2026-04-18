<?php

namespace Database\Factories;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Team;
use App\Support\Enums\TeamAcceptedType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'encounter_id' => Encounter::factory(),
            'movement_team_id' => null,
            'name' => fake()->randomElement(['Louvor', 'Secretaria', 'Cozinha', 'Decoração', 'Acolhida']),
            'min_members' => 2,
            'max_members' => 5,
            'accepted_type' => TeamAcceptedType::All,
            'recommended_skills' => [],
            'order' => 0,
        ];
    }
}
