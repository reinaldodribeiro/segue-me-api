<?php

namespace Database\Factories;

use App\Domain\Parish\Models\Diocese;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DioceseFactory extends Factory
{
    protected $model = Diocese::class;

    public function definition(): array
    {
        $name = fake()->unique()->city().' Diocese';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => null,
            'active' => true,
        ];
    }
}
