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
        // For MySQL, altering ENUM requires raw statements if not using doctrine
        // Replacing the previous ENUM entirely with the new one containing 'ical'
        // ENUM('local', 'booking.com', 'ical')
        
        DB::statement("ALTER TABLE bookings MODIFY COLUMN source ENUM('local', 'booking.com', 'ical') DEFAULT 'local'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM
        DB::statement("ALTER TABLE bookings MODIFY COLUMN source ENUM('local', 'booking.com') DEFAULT 'local'");
    }
};
