<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'latitude' => fake()->latitude(43, 46),
            'longitude' => fake()->longitude(19, 22),
        ];
    }
}
