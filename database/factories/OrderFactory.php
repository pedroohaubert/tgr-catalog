<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
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
