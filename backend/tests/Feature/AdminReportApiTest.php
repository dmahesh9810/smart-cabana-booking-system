<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $adminRole = Role::create(['name' => 'admin']);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);

        $customerRole = Role::create(['name' => 'customer']);
        $customer = User::factory()->create(['role_id' => $customerRole->id]);

        $cabana = Cabana::create(['name' => 'Report Unit', 'price_per_night' => 1000, 'max_guests' => 2, 'is_active' => true]);

        // Create a test booking
        Booking::create([
            'booking_ref' => 'REP-101',
            'user_id' => $customer->id,
            'cabana_id' => $cabana->id,
            'check_in' => Carbon::today()->toDateString(),
            'check_out' => Carbon::tomorrow()->toDateString(),
            'guests_count' => 1,
            'total_amount' => 5000,
            'status' => 'completed'
        ]);

        // Create a test commission for revenue report
        Commission::create([
            'booking_id' => 1,
            'gross_amount' => 5000,
            'commission_rate' => 0.25,
            'commission_amount' => 1250,
            'owner_earnings' => 3750,
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_access_bookings_report()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/reports/bookings');

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.booking_ref', 'REP-101');
    }

    public function test_admin_can_filter_bookings_by_status()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/reports/bookings?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.data');
    }

    public function test_admin_can_access_revenue_report()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/reports/revenue');

        $response->assertStatus(200);
        
        $revenue = $response->json('data.0.gross_revenue');
        $this->assertEquals(5000, $revenue);
    }

    public function test_admin_can_access_occupancy_report()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/reports/occupancy');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.cabana_name', 'Report Unit');
    }
}
