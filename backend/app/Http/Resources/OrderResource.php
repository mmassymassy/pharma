<?php

namespace App\Http\Resources;

use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client')),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total' => (float) $this->total,
            'delivery_name' => $this->delivery_name,
            'delivery_phone' => $this->delivery_phone,
            'delivery_country' => $this->delivery_country,
            'delivery_city' => $this->delivery_city,
            'delivery_address' => $this->delivery_address,
            'tracking_number' => $this->tracking_number,
            'carrier' => $this->carrier,
            'source' => $this->source,
            'notes' => $this->notes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_histories' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'whatsapp_url' => WhatsApp::url(
                $this->delivery_phone,
                "Hello {$this->delivery_name}, regarding your order {$this->reference} (status: {$this->status->label()})."
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
