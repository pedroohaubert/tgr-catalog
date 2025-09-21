<?php

namespace App\DTOs;

use App\Http\Requests\Product\ProductUpdateRequest;

class ProductUpdateData
{
    public function __construct(
        public ?string $name = null,
        public ?string $slug = null,
        public ?float $price = null,
        public ?int $stock = null,
        public ?bool $is_active = null,
    ) {
    }

    public static function fromRequest(ProductUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            name: array_key_exists('name', $data) ? (string) $data['name'] : null,
            slug: array_key_exists('slug', $data) ? (string) $data['slug'] : null,
            price: array_key_exists('price', $data) ? (float) $data['price'] : null,
            stock: array_key_exists('stock', $data) ? (int) $data['stock'] : null,
            is_active: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
        ], static fn ($v) => $v !== null);
    }
}
