<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Booking;
use App\Models\CabanaAvailability;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $customerUser;
    protected $cabana;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);

        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $this->customerUser = User::factory()->create(['role_id' => $customerRole->id]);

        $this->cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'A nice cabana',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true,
        ]);
    }

    public function test_can_check_availability_when_free()
    {
        $response = $this->postJson("/api/v1/cabanas/{$this->cabana->id}/check-availability", [
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
            'success' => true,
            'available' => true,
        ]);
    }

    public function test_cannot_book_inactive_cabana()
    {
        $this->cabana->update(['is_active' => false]);

        $response = $this->postJson("/api/v1/cabanas/{$this->cabana->id}/check-availability", [
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
            'success' => true,
            'available' => false,
        ]);
    }

    public function test_detects_overlapping_booking()
    {
        $checkIn = Carbon::tomorrow();
        $checkOut = Carbon::tomorrow()->addDays(3);

        Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => clone $checkIn,
            'check_out' => clone $checkOut,
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'confirmed'
        ]);

        // Try booking overlapping dates (Starts during another booking)
        $response = $this->postJson("/api/v1/cabanas/{$this->cabana->id}/check-availability", [
            'check_in' => $checkIn->copy()->addDay()->toDateString(),
            'check_out' => $checkOut->copy()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
            'success' => true,
            'available' => false,
        ]);

        // Try booking same check-out as someone else's check-in (valid)
        $validResponse = $this->postJson("/api/v1/cabanas/{$this->cabana->id}/check-availability", [
            // Start earlier than checkIn
            'check_in' => $checkIn->copy()->addDays(5)->toDateString(),
            'check_out' => $checkIn->copy()->addDays(6)->toDateString(),
        ]);

        $validResponse->assertStatus(200)
            ->assertJson([
            'success' => true,
            'available' => true,
        ]);
    }

    public function test_ignores_cancelled_bookings()
    {
        Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'cancelled' // Cancelled booking should not block
        ]);

        $response = $this->postJson("/api/v1/cabanas/{$this->cabana->id}/check-availability", [
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(3)->toDateString(),
        ]);

        $response->assertStatus(200)->assertJsonFragment(['available' => true]);
    }

    public function test_admin_can_block_dates()
    {
        $response = $this->actingAs($this->adminUser)->postJson("/api/v1/admin/cabanas/{$this->cabana->id}/block-dates", [
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'reason' => 'Maintenance'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('cabana_availability', [
            'cabana_id' => $this->cabana->id,
            'reason' => 'Maintenance'
        ]);

        // Ensure availability check fails now
        $checkResponse = $this->postJson("/api/v1/cabanas/{$this->cabana->id}/check-availability", [
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $checkResponse->assertStatus(200)->assertJsonFragment(['available' => false]);
    }

    public function test_customer_cannot_block_dates()
    {
        $response = $this->actingAs($this->customerUser)->postJson("/api/v1/admin/cabanas/{$this->cabana->id}/block-dates", [
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'reason' => 'Maintenance'
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_block_dates_over_existing_booking()
    {
        Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->adminUser)->postJson("/api/v1/admin/cabanas/{$this->cabana->id}/block-dates", [
            'start_date' => Carbon::tomorrow()->addDay()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(5)->toDateString(),
            'reason' => 'Maintenance'
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot block dates that overlap with existing bookings.']);
    }

    public function test_calendar_endpoint_returns_formatted_ranges()
    {
        $start = Carbon::tomorrow();
        $end = Carbon::tomorrow()->addDays(2);

        Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => clone $start,
            'check_out' => clone $end,
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'confirmed'
        ]);

        $response = $this->getJson("/api/v1/cabanas/{$this->cabana->id}/calendar");

        $response->assertStatus(200)
            ->assertJsonStructure([
            'success',
            'data' => [
                'booked' => [
                    '*' => ['date', 'booking_id']
                ],
                'blocked' => []
            ]
        ]);

        // Length should be 2 because it includes start date and excludes end date
        $this->assertCount(2, $response->json('data.booked'));
        $this->assertEquals($start->toDateString(), $response->json('data.booked.0.date'));
    }
}
