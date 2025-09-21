<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        if (! $product instanceof Product) {
            return false;
        }

        return $this->user()?->can('view', $product) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
