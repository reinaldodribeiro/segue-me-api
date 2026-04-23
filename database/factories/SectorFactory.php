<?php

namespace Database\Factories;

use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        $name = 'Setor '.$this->faker->unique()->word();

        return [
            'diocese_id' => Diocese::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'active' => true,
        ];
    }
}
