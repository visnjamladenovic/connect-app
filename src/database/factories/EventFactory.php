<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $totalSeats = fake()->numberBetween(20, 300);

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'category_id' => Category::factory(),
            'city_id' => City::factory(),
            'location_name' => fake()->streetName(),
            'latitude' => fake()->latitude(43, 46),
            'longitude' => fake()->longitude(19, 22),
            'starts_at' => fake()->dateTimeBetween('now', '+3 months'),
            'ticket_price' => fake()->randomFloat(2, 500, 5000),
            'total_seats' => $totalSeats,
            'available_seats' => fake()->numberBetween(0, $totalSeats),
            'created_by' => User::factory(),
        ];
    }
}
