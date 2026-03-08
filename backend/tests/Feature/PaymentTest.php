<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Cabana;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\BookingLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $customerUser;
    protected $otherCustomerUser;
    protected $cabana;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Config Mock
        Config::set('payhere.merchant_id', '12345');
        Config::set('payhere.merchant_secret', 'SECRET');
        Config::set('payhere.currency', 'LKR');

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
            'success' => true,
            'data' => [
                'merchant_id' => '12345',
                'order_id' => 'CAB-20260308-XYZ1',
                'amount' => '300.00',
                'currency' => 'LKR'
            ]
        ]);

        // Has generated a hash
        $this->assertNotNull($response->json('data.hash'));

        // Assert payment record was created
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'PayHere',
            'status' => 'pending'
        ]);
    }

    public function test_cannot_initiate_others_payment()
    {
        $response = $this->actingAs($this->otherCustomerUser)->postJson('/api/v1/payments/initiate', [
            'booking_id' => $this->booking->id
        ]);

        // Handled by firstOrFail on user_id inside service logic
        $response->assertStatus(422);
    }

    public function test_cannot_initiate_confirmed_payment()
    {
        $this->booking->update(['status' => 'confirmed']);

        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/payments/initiate', [
            'booking_id' => $this->booking->id
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Payment can only be initiated for pending bookings.']);
    }

    public function test_webhook_updates_booking_status()
    {
        Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'PayHere',
            'status' => 'pending'
        ]);

        $merchantId = '12345';
        $orderId = 'CAB-20260308-XYZ1';
        $amount = '300.00';
        $currency = 'LKR';
        $statusCode = '2';
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

        $response->dump();

        $response->assertStatus(200);

        // Verify database updates
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'status' => 'successful',
            'transaction_id' => 'PAY123456'
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'confirmed'
        ]);

        $this->assertDatabaseHas('booking_logs', [
            'booking_id' => $this->booking->id,
            'action' => 'payment_received'
        ]);
    }

    public function test_invalid_webhook_rejected()
    {
        Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'PayHere',
            'status' => 'pending'
        ]);

        $response = $this->postJson('/api/v1/payments/payhere-webhook', [
            'merchant_id' => '12345',
            'order_id' => 'CAB-20260308-XYZ1',
            'payhere_amount' => 300,
            'payhere_currency' => 'LKR',
            'status_code' => '2',
            'md5sig' => 'INVALID_HASH',
            'payment_id' => 'PAY123456'
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Invalid signature']);

        // Assert no changes
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'status' => 'pending', // Still pending
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'pending'
        ]);
    }

    public function test_webhook_rejected_for_amount_tampering()
    {
        Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 300,
            'payment_method' => 'PayHere',
            'status' => 'pending'
        ]);

        $merchantId = '12345';
        $orderId = 'CAB-20260308-XYZ1';
        $tamperedAmount = '10.00'; // Only paid 10!
        $currency = 'LKR';
        $statusCode = '2';
        $secret = 'SECRET';

        // Generating a valid hash but for a tampered amount
        $hash = strtoupper(
            md5(
            $merchantId .
            $orderId .
            $tamperedAmount .
            $currency .
            $statusCode .
            strtoupper(md5($secret))
        )
        );

        $response = $this->postJson('/api/v1/payments/payhere-webhook', [
            'merchant_id' => $merchantId,
            'order_id' => $orderId,
            'payhere_amount' => $tamperedAmount,
            'payhere_currency' => $currency,
            'status_code' => $statusCode,
            'md5sig' => $hash,
            'payment_id' => 'PAY123456'
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Amount mismatch']);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'pending' // Remains unconfirmed!
        ]);
    }
}
