<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reference' => $this->reference,
            'description' => $this->description,
            'category' => $this->category,
            'price' => (float) $this->price,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'availability' => $this->availability,
        ];
    }
}
