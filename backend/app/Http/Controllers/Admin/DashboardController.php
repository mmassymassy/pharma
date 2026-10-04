<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $statusCounts = collect(OrderStatus::cases())->mapWithKeys(function (OrderStatus $status) {
            return [$status->value => Order::where('status', $status)->count()];
        });

        return response()->json([
            'status_counts' => $statusCounts,
            'total_clients' => Client::count(),
            'orders_this_month' => Order::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'latest_orders' => OrderResource::collection(
                Order::with('client')->latest()->limit(5)->get()
            ),
        ]);
    }
}
