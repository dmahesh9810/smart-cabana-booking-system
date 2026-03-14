<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Role;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommissionService();
    }

    public function test_it_calculates_correct_commission_and_earnings()
    {
        $role = Role::create(['name' => 'customer']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'Test',
            'price_per_night' => 1000,
            'max_guests' => 2,
            'is_active' => true
        ]);

        $booking = Booking::create([
            'booking_ref' => 'REF123',
            'user_id' => $user->id,
            'cabana_id' => $cabana->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'guests_count' => 1,
            'total_amount' => 10000.00,
            'status' => 'confirmed'
        ]);

        $commission = $this->service->recordCommission($booking);

        $this->assertEquals(10000.00, $commission->gross_amount);
        $this->assertEquals(2500.00, $commission->commission_amount); // 25% of 10000
        $this->assertEquals(7500.00, $commission->owner_earnings);   // 10000 - 2500
        $this->assertEquals('pending', $commission->status);
    }

    public function test_it_prevents_duplicate_commissions_for_same_booking()
    {
        $role = Role::create(['name' => 'customer']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $cabana = Cabana::create(['name' => 'T', 'price_per_night' => 100, 'max_guests' => 1]);
        $booking = Booking::create([
            'booking_ref' => 'REF2',
            'user_id' => $user->id,
            'cabana_id' => $cabana->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
            'guests_count' => 1,
            'total_amount' => 1000,
            'status' => 'confirmed'
        ]);

        $this->service->recordCommission($booking);
        $this->service->recordCommission($booking);

        $this->assertEquals(1, Commission::where('booking_id', $booking->id)->count());
    }
}
