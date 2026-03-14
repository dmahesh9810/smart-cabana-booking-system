<?php

namespace Tests\Feature\Cabanas;

use App\Models\Cabana;
use App\Models\CabanaImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CabanaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_public_can_view_single_active_cabana()
    {
        $cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'Test',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/v1/cabanas/{$cabana->id}");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $cabana->id,
                    'name' => 'Test Cabana'
                ]
            ]);
    }

    public function test_cabana_images_loading()
    {
        $cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'Test',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true
        ]);

        CabanaImage::create([
            'cabana_id' => $cabana->id,
            'image_path' => 'test.jpg',
            'is_primary' => true
        ]);

        $response = $this->getJson("/api/v1/cabanas/{$cabana->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'images',
                    'primary_image'
                ]
            ]);
    }

    public function test_can_check_availability_when_free()
    {
        $cabana = Cabana::create([
            'name' => 'Test Cabana',
            'description' => 'Test',
            'price_per_night' => 100,
            'max_guests' => 2,
            'is_active' => true
        ]);

        $response = $this->postJson("/api/v1/cabanas/{$cabana->id}/check-availability", [
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => true,
            ]);
    }
}
