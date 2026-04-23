<?php

namespace Database\Factories;

use App\Domain\Encounter\Models\Movement;
use App\Domain\Encounter\Models\MovementTeam;
use App\Support\Enums\TeamAcceptedType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementTeamFactory extends Factory
{
    protected $model = MovementTeam::class;

    public function definition(): array
    {
        return [
            'movement_id' => Movement::factory(),
            'name' => $this->faker->randomElement(['Louvor', 'Secretaria', 'Cozinha', 'Decoração', 'Audiovisual']),
            'min_members' => 2,
            'max_members' => 5,
            'accepted_type' => TeamAcceptedType::All,
            'recommended_skills' => [],
            'order' => 0,
        ];
    }
}
