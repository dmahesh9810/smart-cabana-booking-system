<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Booking;
use App\Models\BookingLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $customerUser;
    protected $otherCustomerUser;
    protected $cabana;

    protected function setUp(): void
    {
        parent::setUp();

        $customerRole = Role::create(['name' => 'customer']);

        $this->customerUser = User::factory()->create(['role_id' => $customerRole->id]);
        $this->otherCustomerUser = User::factory()->create(['role_id' => $customerRole->id]);

        $this->cabana = Cabana::create([
            'name' => 'Premium Cabana',
            'description' => 'A nice cabana',
            'price_per_night' => 150,
            'max_guests' => 4,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_create_booking()
    {
        $checkIn = Carbon::tomorrow();
        $checkOut = Carbon::tomorrow()->addDays(3);

        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/bookings', [
            'cabana_id' => $this->cabana->id,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'guests_count' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson([
            'success' => true,
            'message' => 'Booking created successfully',
        ]);

        // Total amount assertion (3 nights * $150 = $450)
        $this->assertEquals(450, $response->json('data.total_amount'));

        // Assert Booking reference format
        $this->assertMatchesRegularExpression('/^CAB-\d{8}-[A-Z0-9]{4}$/', $response->json('data.booking_ref'));

        $this->assertDatabaseHas('bookings', [
            'cabana_id' => $this->cabana->id,
            'user_id' => $this->customerUser->id,
            'total_amount' => 450,
            'status' => 'pending'
        ]);

        $booking = Booking::first();

        // Assert log creation
        $this->assertDatabaseHas('booking_logs', [
            'booking_id' => $booking->id,
            'action' => 'booking_created'
        ]);
    }

    public function test_booking_fails_if_inactive_cabana()
    {
        $this->cabana->update(['is_active' => false]);

        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/bookings', [
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'This cabana is not currently active.']);
    }

    public function test_booking_fails_if_dates_overlap()
    {
        // Add existing booking
        Booking::create([
            'booking_ref' => 'CAB-20260308-ABCD',
            'user_id' => $this->otherCustomerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(4)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 600,
            'status' => 'confirmed'
        ]);

        // Try booking overlapping dates
        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/bookings', [
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->addDay()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'The cabana is no longer available for the selected dates.']);
    }

    public function test_booking_fails_if_guests_exceed_max()
    {
        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/bookings', [
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 5, // Max is 4
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guests_count']);
    }

    public function test_customer_can_view_own_bookings()
    {
        Booking::create([
            'booking_ref' => 'CAB-REFOWN',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'pending'
        ]);

        Booking::create([
            'booking_ref' => 'CAB-REFOTHER',
            'user_id' => $this->otherCustomerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->addDays(5)->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(7)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->customerUser)->getJson('/api/v1/bookings');

        $response->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('CAB-REFOWN', $response->json('data.0.booking_ref'));
    }

    public function test_customer_cannot_view_others_booking()
    {
        $booking = Booking::create([
            'booking_ref' => 'CAB-REFOTHER',
            'user_id' => $this->otherCustomerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->customerUser)->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertStatus(404);
    }
}
