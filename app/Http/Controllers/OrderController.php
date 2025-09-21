<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{
    public function index()
    {
        $paginator = Order::query()
            ->where('user_id', Auth::id())
            ->with(['items.product'])
            ->latest('id')
            ->paginate(15);

        return view('orders.index', ['paginator' => $paginator]);
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
            'user_id' => (int) $order->user_id,
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
