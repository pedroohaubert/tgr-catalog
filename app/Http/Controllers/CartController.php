<?php

namespace App\Http\Controllers;

use App\DTOs\CartItemAddData;
use App\DTOs\CartItemRemoveData;
use App\DTOs\CartItemUpdateData;
use App\Http\Requests\Cart\CartAddRequest;
use App\Http\Requests\Cart\CartRemoveRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        try {
            $summary = $cart->remove($dto->productId);

            return $this->jsonSuccess($summary, 'Item removido.');
        } catch (Throwable $e) {
            return $this->handleDomainException($e);
        }
    }

    public function clear(Request $request, CartService $cart): JsonResponse
    {
        try {
            $summary = $cart->clear();

            return $this->jsonSuccess($summary, 'Carrinho limpo.');
        } catch (Throwable $e) {
            Log::error('Failed to clear cart', [
                'user_id' => $request->user()?->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->jsonError('clear_failed', 'Não foi possível limpar o carrinho. Tente novamente.', null, 500);
        }
    }
}
