<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderCancelRequest extends FormRequest
{
    public function authorize(): bool
    {

        $order = $this->route('order');
        if (! $order instanceof Order) {
            return false;
        }

        return $this->user()?->can('cancel', $order) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
