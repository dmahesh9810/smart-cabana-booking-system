<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_booking_as_completed_triggers_commission_creation()
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $customerRole = Role::create(['name' => 'customer']);
        $customer = User::factory()->create(['role_id' => $customerRole->id]);

        $cabana = Cabana::create(['name' => 'Test', 'price_per_night' => 100, 'max_guests' => 2, 'is_active' => true]);

        $booking = Booking::create([
            'booking_ref' => 'AUTO-COMM',
            'user_id' => $customer->id,
            'cabana_id' => $cabana->id,
            'check_in' => Carbon::yesterday()->toDateString(),
            'check_out' => Carbon::today()->toDateString(),
            'guests_count' => 1,
            'total_amount' => 5000,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/v1/admin/bookings/{$booking->id}/status", [
            'status' => 'completed'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('commissions', [
            'booking_id' => $booking->id,
            'gross_amount' => 5000,
            'commission_amount' => 1250, // 25% of 5000
            'owner_earnings' => 3750
        ]);
    }

    public function test_dashboard_stats_include_commission_metrics()
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $customerRole = Role::create(['name' => 'customer']);
        $customer = User::factory()->create(['role_id' => $customerRole->id]);
        $cabana = Cabana::create(['name' => 'T', 'price_per_night' => 100, 'max_guests' => 1, 'is_active' => true]);

        $booking = Booking::create([
            'booking_ref' => 'TEST-DASH',
            'user_id' => $customer->id,
            'cabana_id' => $cabana->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
            'guests_count' => 1,
            'total_amount' => 1000,
            'status' => 'confirmed'
        ]);

        // Create some sample commissions
        Commission::create([
            'booking_id' => $booking->id,
            'gross_amount' => 1000,
            'commission_rate' => 0.25,
            'commission_amount' => 250,
            'owner_earnings' => 750,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'commission_stats' => [
                        'gross_revenue',
                        'platform_commission',
                        'pending_owner_payouts'
                    ]
                ]
            ]);

        $this->assertEquals(250, $response->json('data.commission_stats.platform_commission'));
    }
}
