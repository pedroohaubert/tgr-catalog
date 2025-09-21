<?php

namespace App\DTOs;

use App\Http\Requests\Cart\CartUpdateRequest;

class CartItemUpdateData
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {
    }

    public static function fromRequest(CartUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            productId: (int) $data['product_id'],
            quantity: (int) $data['quantity'],
        );
    }
}
