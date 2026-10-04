<?php

namespace Tests\Feature\Api\Admin;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_change_order_status(): void
    {
        $order = Order::factory()->for(Client::factory())->create(['status' => OrderStatus::RequestReceived]);

        $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'paid'])
            ->assertUnauthorized();
    }

    public function test_admin_can_change_order_status_and_history_is_recorded(): void
    {
        $admin = User::factory()->create();
        $order = Order::factory()->for(Client::factory())->create(['status' => OrderStatus::RequestReceived]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'availability_check',
        ]);

        $response->assertOk();
        $this->assertEquals('availability_check', $response->json('data.status'));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'availability_check']);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'request_received',
            'to_status' => 'availability_check',
            'changed_by' => 'admin',
        ]);
    }

    public function test_it_rejects_an_invalid_status(): void
    {
        $admin = User::factory()->create();
        $order = Order::factory()->for(Client::factory())->create();

        $this->actingAs($admin)
            ->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'not_a_status'])
            ->assertUnprocessable();
    }

    public function test_admin_can_delete_an_order(): void
    {
        $admin = User::factory()->create();
        $order = Order::factory()->for(Client::factory())->create();

        $this->actingAs($admin)
            ->deleteJson("/api/admin/orders/{$order->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
