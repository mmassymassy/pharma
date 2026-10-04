<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StorePublicOrderRequest;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Support\WhatsApp;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function store(StorePublicOrderRequest $request)
    {
        $data = $request->validated();

        $client = $this->orderService->findOrCreateClientByPhone($data, 'website');

        $order = $this->orderService->createForClient(
            $client,
            $data['items'],
            'website',
            $data['notes'] ?? null,
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'],
            ]
        );

        $itemsList = $order->items->map(
            fn (OrderItem $item) => "{$item->quantity}x {$item->product_name}"
        )->implode(', ');

        $message = "Hello, I just placed order {$order->reference}: {$itemsList}. Name: {$data['name']}, Country: " . ($data['country'] ?? 'N/A') . ". I'd like to confirm.";

        return response()->json([
            'reference' => $order->reference,
            'total' => (float) $order->total,
            'whatsapp_url' => WhatsApp::businessUrl($message),
        ], 201);
    }
}
