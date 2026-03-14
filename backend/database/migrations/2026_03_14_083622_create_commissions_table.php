<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2)->default(0.25);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('owner_earnings', 12, 2);
            $table->enum('status', ['pending', 'paid_to_owner'])->default('pending');
            $table->timestamps();

            $table->index('booking_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
