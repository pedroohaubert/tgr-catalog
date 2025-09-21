<?php

namespace App\DTOs;

use App\Http\Requests\Product\ProductStoreRequest;

class ProductStoreData
{
    public function __construct(
        public string $name,
        public ?string $slug,
        public float $price,
        public int $stock = 0,
        public bool $is_active = true,
    ) {
    }

    public static function fromRequest(ProductStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: (string) ($data['name'] ?? ''),
            slug: array_key_exists('slug', $data) ? (string) $data['slug'] : null,
            price: (float) ($data['price'] ?? 0),
            stock: (int) ($data['stock'] ?? 0),
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
        ];
    }
}
