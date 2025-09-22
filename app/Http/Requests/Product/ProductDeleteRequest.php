<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {

        $product = $this->route('product');
        if (! $product instanceof Product) {
            return false;
        }

        return $this->user()?->can('delete', $product) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
