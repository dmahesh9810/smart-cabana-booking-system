<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if role exists before inserting to avoid duplicates during seeding/migration reruns
        if (DB::table('roles')->where('name', 'staff')->doesntExist()) {
            DB::table('roles')->insert([
                'name' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'staff')->delete();
    }
};
