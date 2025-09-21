<?php

namespace App\DTOs;

use App\Http\Requests\Cart\CartRemoveRequest;

class CartItemRemoveData
{
    public function __construct(
        public int $productId,
    ) {
    }

    public static function fromRequest(CartRemoveRequest $request): self
    {
        $data = $request->validated();

        return new self(
            productId: (int) $data['product_id'],
        );
    }
}
