<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => (string) Str::uuid(),
            'user_id' => User::factory(['role' => UserRole::Client]),
            'total' => fake()->randomFloat(2, 10, 1000),
            'status' => fake()->randomElement(['pending', 'paid', 'canceled']),
        ];
    }
}
