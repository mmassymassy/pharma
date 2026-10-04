<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\OrderResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::withCount('orders')
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('search')->trim();
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('whatsapp', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request)
    {
        $client = Client::create($request->validated());

        return new ClientResource($client);
    }

    public function show(Client $client)
    {
        $client->load(['orders' => fn ($q) => $q->latest()]);

        return (new ClientResource($client))->additional([
            'orders' => OrderResource::collection($client->orders),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->noContent();
    }
}
