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
        Schema::create('channex_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabana_id')->constrained()->onDelete('cascade');
            $table->string('property_id');
            $table->string('room_type_id');
            $table->string('rate_plan_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channex_mappings');
    }
};
