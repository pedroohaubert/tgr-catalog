<?php

namespace App\DTOs;

use App\Http\Requests\Order\OrderCancelRequest;

class OrderCancelData
{
    public function __construct() {}

    public static function fromRequest(OrderCancelRequest $request): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
