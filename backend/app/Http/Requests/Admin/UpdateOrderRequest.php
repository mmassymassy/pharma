<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_name' => ['sometimes', 'required', 'string', 'max:255'],
            'delivery_phone' => ['sometimes', 'required', 'string', 'max:50'],
            'delivery_country' => ['nullable', 'string', 'max:100'],
            'delivery_city' => ['nullable', 'string', 'max:100'],
            'delivery_address' => ['sometimes', 'required', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
