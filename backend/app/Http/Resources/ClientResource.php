<?php

namespace App\Http\Resources;

use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'notes' => $this->notes,
            'source' => $this->source,
            'orders_count' => $this->whenCounted('orders'),
            'whatsapp_url' => WhatsApp::url(
                $this->whatsapp ?: $this->phone,
                "Hello {$this->name}, this is regarding your order with us."
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
