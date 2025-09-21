<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;
class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        if (! $product instanceof Product) {
            return false;
        }

        return $this->user()?->can('update', $product) === true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $ignoreId = $product instanceof Product ? $product->id : null;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => array_filter([
                'sometimes', 'string', 'max:255',
                $ignoreId !== null ? Rule::unique('products', 'slug')->ignore($ignoreId) : null,
            ]),
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}