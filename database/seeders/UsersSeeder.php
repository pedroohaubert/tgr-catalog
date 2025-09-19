<?php
// database/seeders/UsersSeeder.php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'Cliente',
                'password' => Hash::make('password'),
                'role' => UserRole::Client,
            ]
        );
    }
}