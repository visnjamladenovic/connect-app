<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 4);
        $unitPrice = fake()->randomFloat(2, 500, 5000);

        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'quantity' => $quantity,
            'total_price' => $quantity * $unitPrice,
            'status' => fake()->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}
