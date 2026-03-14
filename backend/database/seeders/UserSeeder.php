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

        // Create 20 Customers
        User::factory()->count(20)->create([
            'role_id' => $customerRole->id,
            'password' => Hash::make('password'),
        ]);
    }
}
