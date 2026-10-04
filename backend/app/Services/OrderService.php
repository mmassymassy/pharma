<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order for an existing client, recalculating totals from current product prices.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    public function createForClient(Client $client, array $items, string $source, ?string $notes, array $delivery): Order
    {
        return DB::transaction(function () use ($client, $items, $source, $notes, $delivery) {
            $order = Order::create([
                'reference' => $this->generateReference(),
                'client_id' => $client->id,
                'status' => OrderStatus::RequestReceived,
                'total' => 0,
                'delivery_name' => $delivery['name'],
                'delivery_phone' => $delivery['phone'],
                'delivery_country' => $delivery['country'] ?? null,
                'delivery_city' => $delivery['city'] ?? null,
                'delivery_address' => $delivery['address'],
                'source' => $source,
                'notes' => $notes,
            ]);

            $total = $this->attachItems($order, $items);
            $order->update(['total' => $total]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::RequestReceived->value,
                'changed_by' => $source === 'manual' ? 'admin' : null,
            ]);

            return $order->fresh(['items', 'client']);
        });
    }

    /**
     * Find a client by phone or create a new one (used for public order submissions).
     */
    public function findOrCreateClientByPhone(array $data, string $source): Client
    {
        $client = Client::where('phone', $data['phone'])->first();

        if ($client) {
            return $client;
        }

        return Client::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'source' => $source,
        ]);
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     */
    public function attachItems(Order $order, array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $item['quantity'],
            ]);

            $total += $product->price * $item['quantity'];
        }

        return $total;
    }

    public function changeStatus(Order $order, OrderStatus $newStatus, ?string $changedBy = null): Order
    {
        $from = $order->status;

        $order->update(['status' => $newStatus]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from?->value,
            'to_status' => $newStatus->value,
            'changed_by' => $changedBy,
        ]);

        return $order->fresh(['statusHistories']);
    }

    public function generateReference(): string
    {
        $year = now()->year;
        $count = Order::whereYear('created_at', $year)->count() + 1;

        do {
            $reference = sprintf('ORD-%d-%04d', $year, $count);
            $count++;
        } while (Order::where('reference', $reference)->exists());

        return $reference;
    }
}
