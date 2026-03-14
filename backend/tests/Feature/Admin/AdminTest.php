<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Cabana;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Cabana $cabana;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin']);
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->cabana = Cabana::create([
            'name' => 'Tudor Cabana',
            'description' => 'Classic style',
            'price_per_night' => 120,
            'max_guests' => 3,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_all_bookings()
    {
        Booking::create([
            'booking_ref' => 'REF-A',
            'user_id' => User::factory()->create()->id,
            'cabana_id' => $this->cabana->id,
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'guests_count' => 2,
            'total_amount' => 240,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);

    }

    public function test_admin_can_manage_cabanas()
    {
        // Create
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/cabanas', [
            'name' => 'New Cabana',
            'description' => 'New desc',
            'price_per_night' => 90,
            'max_guests' => 2,
            'location' => 'Garden',
            'is_active' => true
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('cabanas', ['name' => 'New Cabana']);

        $cabanaId = $response->json('data.id');

        // Update
        $this->actingAs($this->admin)->putJson("/api/v1/admin/cabanas/{$cabanaId}", [
            'name' => 'Updated Cabana',
            'description' => 'Updated desc',
            'price_per_night' => 100,
            'max_guests' => 2
        ])->assertStatus(200);
        $this->assertDatabaseHas('cabanas', ['name' => 'Updated Cabana']);

        // Delete
        $this->actingAs($this->admin)->deleteJson("/api/v1/admin/cabanas/{$cabanaId}")
            ->assertStatus(200);
        $this->assertSoftDeleted('cabanas', ['id' => $cabanaId]);
    }

    public function test_admin_dashboard_statistics()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_cabanas',
                    'active_cabanas',
                    'total_bookings'
                ]
            ]);
    }
}
