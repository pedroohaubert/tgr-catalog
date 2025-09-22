<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderPayRequest extends FormRequest
{
    public function authorize(): bool
    {

        $order = $this->route('order');
        if (! $order instanceof Order) {
            return false;
        }

        return $this->user()?->can('pay', $order) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
