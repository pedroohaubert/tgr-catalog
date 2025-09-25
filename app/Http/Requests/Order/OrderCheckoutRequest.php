<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class OrderCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
