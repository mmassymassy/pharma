<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required_without:new_client', 'nullable', 'exists:clients,id'],

            'new_client' => ['required_without:client_id', 'nullable', 'array'],
            'new_client.name' => ['required_with:new_client', 'string', 'max:255'],
            'new_client.phone' => ['required_with:new_client', 'string', 'max:50'],
            'new_client.whatsapp' => ['nullable', 'string', 'max:50'],
            'new_client.country' => ['nullable', 'string', 'max:100'],
            'new_client.city' => ['nullable', 'string', 'max:100'],
            'new_client.address' => ['nullable', 'string', 'max:255'],

            'delivery_name' => ['required', 'string', 'max:255'],
            'delivery_phone' => ['required', 'string', 'max:50'],
            'delivery_country' => ['nullable', 'string', 'max:100'],
            'delivery_city' => ['nullable', 'string', 'max:100'],
            'delivery_address' => ['required', 'string', 'max:255'],

            'source' => ['required', 'in:website,whatsapp,manual'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
