<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = Order::with('client')
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhere('delivery_name', 'like', "%{$search}%")
                        ->orWhere('delivery_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        if (! empty($data['client_id'])) {
            $client = Client::findOrFail($data['client_id']);
        } else {
            $client = Client::create([
                ...$data['new_client'],
                'source' => 'manual',
            ]);
        }

        $order = $this->orderService->createForClient(
            $client,
            $data['items'],
            $data['source'],
            $data['notes'] ?? null,
            [
                'name' => $data['delivery_name'],
                'phone' => $data['delivery_phone'],
                'country' => $data['delivery_country'] ?? null,
                'city' => $data['delivery_city'] ?? null,
                'address' => $data['delivery_address'],
            ]
        );

        return new OrderResource($order->load(['client', 'items', 'statusHistories']));
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['client', 'items', 'statusHistories']));
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return new OrderResource($order->load(['client', 'items', 'statusHistories']));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $order = $this->orderService->changeStatus(
            $order,
            OrderStatus::from($request->validated('status')),
            'admin'
        );

        return new OrderResource($order->load(['client', 'items', 'statusHistories']));
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->noContent();
    }
}
