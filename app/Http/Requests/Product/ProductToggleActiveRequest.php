<?php

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
class ProductToggleActiveRequest extends FormRequest
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
     * Permite forçar um valor específico; se omitido, o service alterna.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'force' => ['sometimes', 'boolean'],
        ];
    }
}