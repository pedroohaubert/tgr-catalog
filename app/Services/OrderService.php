<?php

namespace App\Services;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OrderService
{
    /**
     * Cria um pedido a partir do carrinho atual do usuário.
     * - Transação atômica.
     * - Usa snapshot de preços do carrinho.
     * - Deixa o débito de estoque para a confirmação de pagamento.
     */
    public function createFromCart(User $user, CartService $cart): Order
    {
        $items = $cart->items();
        if (count($items) === 0) {
            throw new InvalidArgumentException('Carrinho vazio.');
        }

        $order = DB::transaction(function () use ($user, $items): Order {
            $order = Order::query()->create([
                'code' => (string) Str::uuid(),
                'user_id' => $user->id,
                'total' => 0.0,
                'status' => 'pending',
            ]);

            $total = 0.0;

            foreach ($items as $i) {
                $qty = (int) $i['quantity'];
                $unit = (float) $i['unit_price'];
                $subtotal = round($unit * $qty, 2);
                $total += $subtotal;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => (int) $i['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $unit,
                ]);
            }

            $order->total = round($total, 2);
            $order->save();

            return $order->refresh()->load('items');
        });

        DB::afterCommit(function () use ($cart): void {
            $cart->clear();
        });

        return $order;
    }

    /**
     * Confirma o pagamento de um pedido (idempotente).
     * - Recalcula total a partir dos itens do pedido.
     * - Bloqueia linhas de produtos (FOR UPDATE) e debita estoque.
     * - Nunca permite estoque negativo. Em conflito: lança 409.
     * - Event `OrderPaid` disparado após commit.
     */
    public function confirmPayment(Order $order): Order
    {
        if ($order->status === 'paid') {
            return $order->load('items');
        }

        if ($order->status !== 'pending') {
            throw new LogicException('Pedido não pode ser pago no status atual.');
        }

        $updated = DB::transaction(function () use ($order): Order {
            $order = Order::query()
                ->lockForUpdate()
                ->with('items')
                ->findOrFail($order->id);

            if ($order->status === 'paid') {
                return $order;
            }

            $productIds = $order->items->pluck('product_id')->all();

            /** @var Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($order->items as $item) {
                /** @var Product $product */
                $product = $products->get($item->product_id);
                if ($product === null) {
                    throw new ConflictHttpException('Produto não encontrado para item do pedido.');
                }

                $qty = (int) $item->quantity;

                if ($qty > $product->stock) {
                    throw new ConflictHttpException('Estoque insuficiente para o produto: '.$product->name);
                }

                $product->stock = $product->stock - $qty;
                $product->save();
            }

            $recalculated = 0.0;
            foreach ($order->items as $item) {
                $recalculated += round(((float) $item->unit_price) * (int) $item->quantity, 2);
            }

            $order->total = round($recalculated, 2);
            $order->status = 'paid';
            $order->save();

            return $order->refresh()->load('items');
        });

        DB::afterCommit(function () use ($updated): void {
            event(new OrderPaid($updated));
        });

        return $updated;
    }
}