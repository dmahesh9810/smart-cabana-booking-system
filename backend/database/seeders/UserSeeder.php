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
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
        $staffRole = \App\Models\Role::firstOrCreate(['name' => 'staff']);
        $customerRole = \App\Models\Role::firstOrCreate(['name' => 'customer']);

        // Create 1 Admin
        User::firstOrCreate(
            ['email' => 'admin@smartcabana.lk'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        // Create 2 Staff
        User::factory()->count(2)->create([
            'role_id' => $staffRole->id,
            'password' => Hash::make('staff123'),
        ]);

        // Create 30 Customers
        User::factory()->count(30)->create([
            'role_id' => $customerRole->id,
            'password' => Hash::make('password'),
        ]);
    }
}
