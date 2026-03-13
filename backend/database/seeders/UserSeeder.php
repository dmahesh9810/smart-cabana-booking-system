<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Michael Johnson', 'email' => 'michael@example.com'],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com'],
            ['name' => 'William Brown', 'email' => 'william@example.com'],
            ['name' => 'Olivia Wilson', 'email' => 'olivia@example.com'],
            ['name' => 'James Taylor', 'email' => 'james@example.com'],
            ['name' => 'Sophia Anderson', 'email' => 'sophia@example.com'],
            ['name' => 'Benjamin Thomas', 'email' => 'benjamin@example.com'],
            ['name' => 'Isabella Jackson', 'email' => 'isabella@example.com'],
        ];

        $customerRoleId = \App\Models\Role::where('name', 'customer')->value('id') ?? 2;

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $customerRoleId,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
