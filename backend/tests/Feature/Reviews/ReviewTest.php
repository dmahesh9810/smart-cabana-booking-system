<?php

namespace Tests\Feature\Reviews;

use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cabana $cabana;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'A nice cabana',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true,
        ]);
    }

    private function createBooking(string $status): Booking
    {
        return Booking::create([
            'booking_ref' => 'REF-' . uniqid(),
            'user_id' => $this->user->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->subDays(3)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 200,
            'status' => $status,
        ]);
    }

    public function test_user_can_review_only_after_completed_booking()
    {
        $booking = $this->createBooking('completed');

        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 5,
            'comment' => 'Excellent!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'rating' => 5
        ]);

        $pendingBooking = $this->createBooking('pending');
        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$pendingBooking->id}/reviews", [
            'rating' => 4,
            'comment' => 'Wait...',
        ]);

        $response->assertStatus(422);
    }

    public function test_prevent_multiple_reviews_per_booking()
    {
        $booking = $this->createBooking('completed');

        // First review
        $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 5,
            'comment' => 'First!',
        ])->assertStatus(201);

        // Second review attempt
        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 4,
            'comment' => 'Second...',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(1, Review::where('booking_id', $booking->id)->count());
    }
}
