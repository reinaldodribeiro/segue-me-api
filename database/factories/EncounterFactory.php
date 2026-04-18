<?php

namespace Database\Factories;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Models\Movement;
use App\Domain\Parish\Models\Parish;
use App\Models\User;
use App\Support\Enums\EncounterStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class EncounterFactory extends Factory
{
    protected $model = Encounter::class;

    public function definition(): array
    {
        $parish = Parish::factory()->create();

        return [
            'parish_id' => $parish->id,
            'movement_id' => Movement::factory(),
            'responsible_user_id' => User::factory()->create(['parish_id' => $parish->id])->id,
            'name' => fake()->randomElement(['42º Segue-me', '15º Renascer', '8º ECC']),
            'edition_number' => fake()->numberBetween(1, 50),
            'date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'location' => fake()->address(),
            'status' => EncounterStatus::Draft,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attr) => ['status' => EncounterStatus::Draft]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attr) => ['status' => EncounterStatus::Confirmed]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attr) => ['status' => EncounterStatus::Completed]);
    }
}
