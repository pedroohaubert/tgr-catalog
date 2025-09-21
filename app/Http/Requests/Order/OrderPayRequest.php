<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class OrderPayRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Order|null $order */
        $order = $this->route('order');
        if (! $order instanceof Order) {
            return false;
        }

        return $this->user()?->can('pay', $order) === true;
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
