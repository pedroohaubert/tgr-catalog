<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
class ProductDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        if (! $product instanceof Product) {
            return false;
        }

        return $this->user()?->can('delete', $product) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
