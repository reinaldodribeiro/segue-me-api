<?php

namespace Database\Factories;

use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ParishFactory extends Factory
{
    protected $model = Parish::class;

    public function definition(): array
    {
        $name = 'Paróquia '.fake()->unique()->name();

        return [
            'sector_id' => Sector::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => null,
            'primary_color' => '#2e6da4',
            'secondary_color' => '#4a9fd4',
            'active' => true,
        ];
    }
}
