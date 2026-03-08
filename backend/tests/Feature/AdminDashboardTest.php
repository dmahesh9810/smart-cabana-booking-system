<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Cabana $cabana;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);

        $this->cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'A nice cabana',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true,
        ]);

        // Seed some data for stats
        $booking = Booking::create([
            'booking_ref' => 'B-12345',
            'user_id' => $this->customer->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 200,
            'status' => 'confirmed'
        ]);

        Payment::create([
            'transaction_id' => 'TXN-12345',
            'booking_id' => $booking->id,
            'amount' => 200,
            'status' => 'successful',
            'payment_method' => 'card',
        ]);
    }

    public function test_admin_can_view_dashboard_stats()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
            'success',
            'data' => [
                'total_cabanas',
                'active_cabanas',
                'total_bookings',
                'confirmed_bookings',
                'total_revenue',
                'average_rating',
                'upcoming_bookings'
            ]
        ]);

        // Assert calculation correctness based on seeded data
        $response->assertJsonPath('data.total_cabanas', 1)
            ->assertJsonPath('data.active_cabanas', 1)
            ->assertJsonPath('data.total_bookings', 1)
            ->assertJsonPath('data.confirmed_bookings', 1)
            ->assertJsonPath('data.total_revenue', 200)
            ->assertJsonPath('data.upcoming_bookings', 1);
    }

    public function test_non_admin_cannot_access_dashboard()
    {
        $response = $this->actingAs($this->customer)->getJson('/api/v1/admin/dashboard/stats');
        $response->assertStatus(403);

        $responseBookings = $this->actingAs($this->customer)->getJson('/api/v1/admin/bookings');
        $responseBookings->assertStatus(403);
    }

    public function test_admin_can_view_booking_reports()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
            'data' => [
                '*' => [
                    'booking_ref',
                    'customer_name',
                    'cabana_name',
                    'check_in',
                    'check_out',
                    'status',
                    'payment_status'
                ]
            ],
            'links',
            'meta'
        ]);
    }

    public function test_admin_can_view_payment_reports()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
            'data' => [
                '*' => [
                    'transaction_id',
                    'booking_ref',
                    'amount',
                    'status',
                    'created_at',
                ]
            ],
            'links',
            'meta'
        ]);
    }
}
