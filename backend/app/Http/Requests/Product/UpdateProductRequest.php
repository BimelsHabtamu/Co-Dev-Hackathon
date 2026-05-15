<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('manager') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'category'      => ['sometimes', 'string', 'max:255'],
            'sku'           => ['sometimes', 'string', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'cost_price'    => ['sometimes', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'numeric', 'min:0'],
            'stock_quantity'=> ['sometimes', 'integer', 'min:0'],
        ];
    }
}
