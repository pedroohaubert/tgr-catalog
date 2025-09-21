<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Order\OrderIndexRequest;
use App\Http\Requests\Order\OrderPayRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Throwable;

class OrderController extends ApiController
{
    public function index(OrderIndexRequest $request): JsonResponse
    {
        $paginator = Order::query()
            ->with(['user', 'items.product'])
            ->latest('id')
            ->paginate(20);

        // FUTURE: This will render the admin orders page listing with filters and pagination.
        // Example: return view('admin.orders.index', ['paginator' => $paginator]);
        return $this->jsonSuccess($this->paginateOrders($paginator));
    }

    public function pay(OrderPayRequest $request, Order $order, OrderService $orders): JsonResponse
    {
        try {
            $updated = $orders->confirmPayment($order);

            // FUTURE: After confirming payment, redirect back to order detail page with success flash.
            // Example: return redirect()->route('admin.orders.show', $updated)->with('status', 'paid');
            return $this->jsonSuccess(['order' => $this->transformOrder($updated)], 'Pagamento confirmado.');
        } catch (Throwable $e) {
            return $this->handleDomainException($e);
        }
    }

    private function paginateOrders(LengthAwarePaginator $paginator): array
    {
        return [
            'items' => array_map(fn ($o) => $this->transformOrder($o), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    private function transformOrder(object $order): array
    {
        return [
            'id' => (int) $order->id,
            'code' => (string) $order->code,
            'user' => $order->relationLoaded('user') && $order->user ? [
                'id' => (int) $order->user->id,
                'name' => (string) $order->user->name,
                'email' => (string) $order->user->email,
            ] : null,
            'total' => (float) $order->total,
            'status' => (string) $order->status,
            'items' => array_map(function (object $item): array {
                return [
                    'product_id' => (int) $item->product_id,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'product' => $item->relationLoaded('product') && $item->product
                        ? [
                            'id' => (int) $item->product->id,
                            'name' => (string) $item->product->name,
                            'slug' => (string) $item->product->slug,
                        ]
                        : null,
                ];
            }, $order->items->all()),
            'created_at' => optional($order->created_at)?->toISOString(),
            'updated_at' => optional($order->updated_at)?->toISOString(),
        ];
    }
}
