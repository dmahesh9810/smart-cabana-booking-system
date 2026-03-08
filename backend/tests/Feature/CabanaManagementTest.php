<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Cabana;
use App\Models\CabanaImage;
use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CabanaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);

        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $this->customerUser = User::factory()->create(['role_id' => $customerRole->id]);
    }

    public function test_admin_can_create_cabana()
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/v1/admin/cabanas', [
            'name' => 'Ocean View Cabana',
            'description' => 'Beautiful ocean view.',
            'price_per_night' => 150.00,
            'max_guests' => 2,
            'location' => 'Beachfront',
            'is_active' => true
        ]);
        $response->assertStatus(201)
            ->assertJson([
            'success' => true,
            'message' => 'Cabana created successfully'
        ]);

        $this->assertDatabaseHas('cabanas', ['name' => 'Ocean View Cabana']);
    }

    public function test_customer_cannot_create_cabana()
    {
        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/admin/cabanas', [
            'name' => 'Ocean View Cabana',
            'description' => 'Beautiful ocean view.',
            'price_per_night' => 150.00,
            'max_guests' => 2
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_upload_cabana_image()
    {
        Storage::fake('public');

        $cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'Test',
            'price_per_night' => 100,
            'max_guests' => 2
        ]);

        $response = $this->actingAs($this->adminUser)->postJson("/api/v1/admin/cabanas/{$cabana->id}/images", [
            'image' => UploadedFile::fake()->create('cabana.jpg', 100, 'image/jpeg'),
            'is_primary' => true
        ]);

        $response->assertStatus(201)
            ->assertJson([
            'success' => true,
            'message' => 'Image uploaded successfully'
        ]);

        $this->assertDatabaseHas('cabana_images', [
            'cabana_id' => $cabana->id,
            'is_primary' => 1
        ]);

        $image = CabanaImage::first();
        Storage::disk('public')->assertExists($image->image_path);
    }

    public function test_public_can_view_active_cabanas()
    {
        Cabana::create([
            'name' => 'Active Cabana',
            'description' => 'Active',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true
        ]);

        Cabana::create([
            'name' => 'Inactive Cabana',
            'description' => 'Inactive',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => false
        ]);

        $response = $this->getJson('/api/v1/cabanas');
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Active Cabana'])
            ->assertJsonMissing(['name' => 'Inactive Cabana']);
    }

    public function test_admin_can_sync_amenities()
    {
        $cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'Test',
            'price_per_night' => 100,
            'max_guests' => 2
        ]);

        $amenity1 = Amenity::create(['name' => 'WiFi', 'icon' => 'wifi']);
        $amenity2 = Amenity::create(['name' => 'Pool', 'icon' => 'pool']);

        $response = $this->actingAs($this->adminUser)->postJson("/api/v1/admin/cabanas/{$cabana->id}/amenities", [
            'amenities' => [$amenity1->id, $amenity2->id]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cabana_amenities', [
            'cabana_id' => $cabana->id,
            'amenity_id' => $amenity1->id
        ]);
    }
}
