<?php

namespace Tests\Feature\Bookings;

use App\Models\Role;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $customerUser;
    protected $cabana;

    protected function setUp(): void
    {
        parent::setUp();

        $customerRole = Role::create(['name' => 'customer']);
        $this->customerUser = User::factory()->create(['role_id' => $customerRole->id]);

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

        $this->assertDatabaseHas('bookings', [
            'cabana_id' => $this->cabana->id,
            'user_id' => $this->customerUser->id,
            'total_amount' => 450,
            'status' => 'pending'
        ]);
    }

    public function test_booking_fails_if_dates_overlap()
    {
        // Add existing confirmed booking
        Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => User::factory()->create()->id,
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

    public function test_correct_total_price_calculation()
    {
        $checkIn = Carbon::tomorrow();
        $checkOut = Carbon::tomorrow()->addDays(5); // 5 nights

        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/bookings', [
            'cabana_id' => $this->cabana->id,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'guests_count' => 1,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(750, $response->json('data.total_amount')); // 5 * 150
    }

    public function test_admin_can_cancel_booking()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $booking = Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/bookings/{$booking->id}/status", [
            'status' => 'cancelled'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);
    }
}
