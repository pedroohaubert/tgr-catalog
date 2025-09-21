<?php

namespace App\Http\Controllers;

use App\DTOs\CartItemAddData;
use App\DTOs\CartItemRemoveData;
use App\DTOs\CartItemUpdateData;
use App\Http\Requests\Cart\CartAddRequest;
use App\Http\Requests\Cart\CartClearRequest;
use App\Http\Requests\Cart\CartRemoveRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Throwable;

class CartController extends ApiController
{
    public function summary(CartService $cart): JsonResponse
    {
        return $this->jsonSuccess($cart->summary());
    }

    public function add(CartAddRequest $request, CartService $cart): JsonResponse
    {
        $dto = CartItemAddData::fromRequest($request);

        try {
            $summary = $cart->add($dto->productId, $dto->quantity);

            return $this->jsonSuccess($summary, 'Item adicionado.');
        } catch (Throwable $e) {
            return $this->handleDomainException($e);
        }
    }

    public function update(CartUpdateRequest $request, CartService $cart): JsonResponse
    {
        $dto = CartItemUpdateData::fromRequest($request);

        try {
            $summary = $cart->update($dto->productId, $dto->quantity);

            return $this->jsonSuccess($summary, 'Carrinho atualizado.');
        } catch (Throwable $e) {
            return $this->handleDomainException($e);
        }
    }

    public function remove(CartRemoveRequest $request, CartService $cart): JsonResponse
    {
        $dto = CartItemRemoveData::fromRequest($request);
        $summary = $cart->remove($dto->productId);

        return $this->jsonSuccess($summary, 'Item removido.');
    }

    public function clear(CartClearRequest $request, CartService $cart): JsonResponse
    {
        $summary = $cart->clear();

        return $this->jsonSuccess($summary, 'Carrinho limpo.');
    }
}
