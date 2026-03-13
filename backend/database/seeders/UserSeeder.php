<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $customerRole = Role::where('name', 'customer')->first();

        if ($adminRole) {
            User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '1234567890',
                'role_id' => $adminRole->id
            ]
            );
        }

        if ($customerRole) {
            User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Sample Customer',
                'password' => Hash::make('password'),
                'phone' => '0987654321',
                'role_id' => $customerRole->id
            ]
            );
        }
    }
}
