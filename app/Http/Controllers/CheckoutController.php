<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\OrderCheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Throwable;

class CheckoutController extends ApiController
{
    public function store(OrderCheckoutRequest $request, CartService $cart, OrderService $orders): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user === null) {
                return $this->jsonError('unauthorized', 'Não autenticado.', null, 401);
            }

            $order = $orders->createFromCart($user, $cart);

            return $this->jsonSuccess([
                'order' => [
                    'id' => (int) $order->id,
                    'code' => (string) $order->code,
                    'total' => (float) $order->total,
                    'status' => (string) $order->status,
                ],
            ], 'Pedido criado com sucesso.', 201);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError('invalid_argument', $e->getMessage(), null, 400);
        } catch (Throwable $e) {
            return $this->handleDomainException($e);
        }
    }
}
