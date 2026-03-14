<?php

namespace Tests\Feature\Payments;

use App\Models\Role;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $customerUser;
    protected $cabana;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.payhere.merchant_id', '12345');
        Config::set('services.payhere.secret', 'SECRET');

        $customerRole = Role::create(['name' => 'customer']);
        $this->customerUser = User::factory()->create(['role_id' => $customerRole->id]);

        $this->cabana = Cabana::create([
            'name' => 'Premium Cabana',
            'description' => 'A nice cabana',
            'price_per_night' => 150,
            'max_guests' => 4,
            'is_active' => true,
        ]);

        $this->booking = Booking::create([
            'booking_ref' => 'CAB-20260308-XYZ1',
            'user_id' => $this->customerUser->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 300,
            'status' => 'pending'
        ]);
    }

    public function test_customer_can_initiate_payment()
    {
        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/payments/initiate', [
            'booking_id' => $this->booking->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_status' => 'pending'
        ]);
    }

    public function test_webhook_simulation_updates_payment_status()
    {
        $payment = Payment::create([
            'booking_id' => $this->booking->id,
            'order_id' => $this->booking->booking_ref,
            'amount' => 300,
            'currency' => 'LKR',
            'payment_method' => 'PayHere',
            'payment_status' => 'pending'
        ]);

        $merchantId = '12345';
        $orderId = $this->booking->booking_ref;
        $amount = '300.00';
        $currency = 'LKR';
        $statusCode = '2'; // Success code for PayHere
        $secret = 'SECRET';

        // Hash generation mirroring PayHere
        $hash = strtoupper(
            md5(
                $merchantId .
                $orderId .
                $amount .
                $currency .
                $statusCode .
                strtoupper(md5($secret))
            )
        );

        $response = $this->postJson('/api/v1/payments/payhere-webhook', [
            'merchant_id' => $merchantId,
            'order_id' => $orderId,
            'payhere_amount' => $amount,
            'payhere_currency' => $currency,
            'status_code' => $statusCode,
            'md5sig' => $hash,
            'payment_id' => 'PAY123456'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_status' => 'paid',
            'payhere_payment_id' => 'PAY123456'
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'confirmed'
        ]);
    }

    public function test_payment_belongs_to_booking()
    {
        $payment = Payment::create([
            'booking_id' => $this->booking->id,
            'order_id' => 'ORDER-1',
            'amount' => 100,
            'currency' => 'LKR',
            'payment_method' => 'Manual',
            'payment_status' => 'paid'
        ]);

        $this->assertEquals($this->booking->id, $payment->booking->id);
    }
}
