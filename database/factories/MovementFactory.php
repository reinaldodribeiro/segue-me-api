<?php

namespace Database\Factories;

use App\Domain\Encounter\Models\Movement;
use App\Support\Enums\MovementScope;
use App\Support\Enums\TeamAcceptedType;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    protected $model = Movement::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Segue-me', 'Renascer', 'Encontro de Casais', 'Emaús']),
            'target_audience' => TeamAcceptedType::Youth,
            'scope' => MovementScope::Parish,
            'description' => $this->faker->sentence(),
            'active' => true,
        ];
    }
}
