<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use InvalidArgumentException;
use RuntimeException;

class CartService
{
    private const SESSION_KEY = 'cart.items';

    public function __construct(public Session $session)
    {
    }

    public function add(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantidade deve ser pelo menos 1.');
        }

        $product = Product::query()->findOrFail($productId);

        if ($product->is_active === false) {
            throw new RuntimeException('Produto inativo.');
        }

        $items = $this->load();

        $existingQty = $items[$productId]['quantity'] ?? 0;
        $newQty = $existingQty + $quantity;

        $this->assertStock($product, $newQty);

        $items[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => (float) $product->price, // snapshot
            'quantity' => $newQty,
        ];

        $this->persist($items);

        return $this->summary();
    }

    public function update(int $productId, int $quantity): array
    {
        $items = $this->load();

        if ($quantity <= 0) {
            unset($items[$productId]);
            $this->persist($items);
            return $this->summary();
        }

        $product = Product::query()->findOrFail($productId);

        if ($product->is_active === false) {
            throw new RuntimeException('Produto inativo.');
        }

        $this->assertStock($product, $quantity);

        $unitPrice = isset($items[$productId]) ? (float) $items[$productId]['unit_price'] : (float) $product->price;

        $items[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
        ];

        $this->persist($items);

        return $this->summary();
    }

    public function remove(int $productId): array
    {
        $items = $this->load();
        unset($items[$productId]);
        $this->persist($items);

        return $this->summary();
    }

    public function clear(): array
    {
        $this->persist([]);
        return $this->summary();
    }

    public function items(): array
    {
        $items = $this->load();

        return array_values(array_map(function (array $item): array {
            $subtotal = round(((float) $item['unit_price']) * $item['quantity'], 2);

            return [
                'product_id' => (int) $item['product_id'],
                'name' => (string) $item['name'],
                'unit_price' => (float) $item['unit_price'],
                'quantity' => (int) $item['quantity'],
                'subtotal' => $subtotal,
            ];
        }, $items));
    }

    public function countItems(): int
    {
        $items = $this->load();

        $sum = 0;
        foreach ($items as $item) {
            $sum += (int) $item['quantity'];
        }

        return $sum;
    }

    public function total(): float
    {
        $total = 0.0;

        foreach ($this->items() as $item) {
            $total += $item['subtotal'];
        }

        return round($total, 2);
    }

    public function summary(): array
    {
        return [
            'items' => $this->items(),
            'count' => $this->countItems(),
            'total' => $this->total(),
        ];
    }

    private function load(): array
    {
        $items = $this->session->get(self::SESSION_KEY, []);

        return $items;
    }

    private function persist(array $items): void
    {
        $this->session->put(self::SESSION_KEY, $items);
        $this->session->save();
    }

    private function assertStock(Product $product, int $desiredQty): void
    {
        if ($desiredQty > $product->stock) {
            throw new RuntimeException('Quantidade solicitada excede o estoque disponível.');
        }
    }
}