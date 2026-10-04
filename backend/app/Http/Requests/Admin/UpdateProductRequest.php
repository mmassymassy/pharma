<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'reference' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'reference')->ignore($this->route('product'))],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'buy_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'availability' => ['sometimes', 'required', 'in:in_stock,on_request,unavailable'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
