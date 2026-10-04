<?php

namespace Tests\Feature\Api\Public;

use App\Models\Client;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order_and_a_new_client(): void
    {
        $product = Product::factory()->create(['price' => 20, 'is_active' => true]);

        $response = $this->postJson('/api/public/orders', [
            'name' => 'Jane Doe',
            'phone' => '+15551234567',
            'country' => 'USA',
            'city' => 'New York',
            'address' => '123 Main St',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['reference', 'total', 'whatsapp_url']);
        $this->assertEquals(60, $response->json('total'));
        $this->assertStringContainsString('wa.me/', $response->json('whatsapp_url'));

        $this->assertDatabaseHas('clients', ['phone' => '+15551234567', 'source' => 'website']);
        $this->assertDatabaseHas('orders', ['status' => 'request_received', 'total' => 60]);
        $this->assertDatabaseHas('order_status_histories', ['to_status' => 'request_received', 'from_status' => null]);
    }

    public function test_it_reuses_an_existing_client_by_phone(): void
    {
        $client = Client::factory()->create(['phone' => '+15551234567']);
        $product = Product::factory()->create(['price' => 10]);

        $this->postJson('/api/public/orders', [
            'name' => $client->name,
            'phone' => '+15551234567',
            'address' => '123 Main St',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        $this->assertDatabaseCount('clients', 1);
    }

    public function test_it_recalculates_totals_server_side_and_ignores_client_supplied_price(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);

        $response = $this->postJson('/api/public/orders', [
            'name' => 'Jane Doe',
            'phone' => '+15551234567',
            'address' => '123 Main St',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'price' => 1],
            ],
        ]);

        $response->assertCreated();
        $this->assertEquals(199.98, $response->json('total'));
    }

    public function test_it_validates_required_fields(): void
    {
        $this->postJson('/api/public/orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'address', 'items']);
    }

    public function test_it_is_rate_limited(): void
    {
        $product = Product::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/public/orders', [
                'name' => 'Jane Doe',
                'phone' => '+1555000' . $i,
                'address' => '123 Main St',
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);
        }

        $response = $this->postJson('/api/public/orders', [
            'name' => 'Jane Doe',
            'phone' => '+15550009999',
            'address' => '123 Main St',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(429);
    }
}
