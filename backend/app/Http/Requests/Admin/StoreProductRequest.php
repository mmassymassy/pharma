<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['required', 'string', 'max:255'],
            'reference' => ['required', 'string', 'max:100', 'unique:products,reference'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'buy_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'availability' => ['required', 'in:in_stock,on_request,unavailable'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
