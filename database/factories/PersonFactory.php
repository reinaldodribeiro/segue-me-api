<?php

namespace Database\Factories;

use App\Domain\Parish\Models\Parish;
use App\Domain\People\Models\Person;
use App\Support\Enums\PersonType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'parish_id' => Parish::factory(),
            'type' => PersonType::Youth,
            'name' => $this->faker->name(),
            'partner_name' => null,
            'photo' => null,
            'birth_date' => $this->faker->date('Y-m-d', '-18 years'),
            'phones' => [$this->faker->numerify('119########')],
            'email' => $this->faker->unique()->safeEmail(),
            'skills' => [],
            'notes' => null,
            'engagement_score' => 0,
            'active' => true,
        ];
    }

    public function couple(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PersonType::Couple,
            'partner_name' => $this->faker->name(),
            'wedding_date' => $this->faker->date('Y-m-d', '-1 year'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }

    public function withSkills(array $skills): static
    {
        return $this->state(fn (array $attributes) => ['skills' => $skills]);
    }
}
