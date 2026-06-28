<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Event;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserInterest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Gradovi (tačne koordinate)
        $belgrade = City::create([
            'name' => 'Beograd',
            'latitude' => 44.7866,
            'longitude' => 20.4489,
        ]);

        $cacak = City::create([
            'name' => 'Čačak',
            'latitude' => 43.8914,
            'longitude' => 20.3497,
        ]);

        // 2. Kategorije
        $categories = collect([
            'Muzika',
            'Sport',
            'Pozorište',
            'Izložbe',
            'Edukacija',
            'Zabava',
        ])->map(fn ($name) => Category::create(['name' => $name]));

        // 3. Admin korisnik
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@connect.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 4. Test registrovani korisnici
        $users = User::factory(10)->create([
            'role' => 'registered',
        ]);

        // 5. Interesovanja korisnika (nasumično 1-3 kategorije po korisniku)
        $users->each(function ($user) use ($categories) {
            $categories->random(rand(1, 3))->each(function ($category) use ($user) {
                UserInterest::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                ]);
            });
        });

        // 6. Događaji — 15 u Beogradu, 10 u Čačku
        $events = collect();

        $events = $events->merge(
            Event::factory(15)->create([
                'city_id' => $belgrade->id,
                'category_id' => fn () => $categories->random()->id,
                'created_by' => $admin->id,
            ])
        );

        $events = $events->merge(
            Event::factory(10)->create([
                'city_id' => $cacak->id,
                'category_id' => fn () => $categories->random()->id,
                'created_by' => $admin->id,
            ])
        );

        // 7. Tiketi — svaki korisnik rezerviše 1-3 nasumična događaja
        $users->each(function ($user) use ($events) {
            $events->random(rand(1, 3))->each(function ($event) use ($user) {
                Ticket::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'quantity' => rand(1, 3),
                    'total_price' => $event->ticket_price * rand(1, 3),
                    'status' => fake()->randomElement(['pending', 'paid', 'cancelled']),
                ]);
            });
        });

        // 8. Recenzije — nasumični korisnici ostavljaju recenzije na nasumične događaje
        $events->random(min(15, $events->count()))->each(function ($event) use ($users) {
            Review::create([
                'user_id' => $users->random()->id,
                'event_id' => $event->id,
                'rating' => rand(1, 5),
                'comment' => fake()->sentence(),
            ]);
        });
    }
}
