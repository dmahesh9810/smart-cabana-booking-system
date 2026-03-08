<?php

namespace Tests\Feature;

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

    private function createBooking(int $userId, string $status, string $ref = 'REF123'): Booking
    {
        return Booking::create([
            'booking_ref' => $ref,
            'user_id' => $userId,
            'cabana_id' => $this->cabana->id,
            'check_in' => now()->addDays(1)->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 200,
            'status' => $status,
        ]);
    }

    public function test_user_can_review_completed_booking()
    {
        $booking = $this->createBooking($this->user->id, 'completed');

        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 5,
            'comment' => 'Great experience!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'rating',
                'comment',
                'reviewer_name',
                'created_at',
            ]
        ]);

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'user_id' => $this->user->id,
            'cabana_id' => $this->cabana->id,
            'rating' => 5,
            'comment' => 'Great experience!',
        ]);
    }

    public function test_user_cannot_review_pending_booking()
    {
        $booking = $this->createBooking($this->user->id, 'pending');

        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 4,
            'comment' => 'Nice!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);

        $this->assertDatabaseMissing('reviews', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_user_cannot_review_another_users_booking()
    {
        $otherUser = User::factory()->create();
        $booking = $this->createBooking($otherUser->id, 'completed');

        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);

        $this->assertDatabaseMissing('reviews', [
            'booking_id' => $booking->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_only_one_review_per_booking()
    {
        $booking = $this->createBooking($this->user->id, 'completed');

        // First review
        $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 5,
            'comment' => 'First review!',
        ])->assertStatus(201);

        // Second review attempt
        $response = $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 4,
            'comment' => 'Second review!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_id']);

        $this->assertEquals(1, Review::where('booking_id', $booking->id)->count());
    }

    public function test_can_fetch_cabana_reviews()
    {
        $booking = $this->createBooking($this->user->id, 'completed');

        $this->actingAs($this->user)->postJson("/api/v1/bookings/{$booking->id}/reviews", [
            'rating' => 5,
            'comment' => 'Awesome!',
        ])->assertStatus(201);

        $response = $this->getJson("/api/v1/cabanas/{$this->cabana->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.comment', 'Awesome!');
    }
}
