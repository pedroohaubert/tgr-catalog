<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductService
{
    public function paginate(?string $query = null, ?bool $onlyActive = null, int $perPage = 15): LengthAwarePaginator
    {
        $currentPage = Paginator::resolveCurrentPage();

        $cacheKey = 'products:paginate:'.md5(json_encode([
            'q' => $query,
            'onlyActive' => $onlyActive,
            'perPage' => $perPage,
            'page' => $currentPage,
            'path' => request()->path(),
        ]));

        $paginator = Cache::remember($cacheKey, 60, function () use ($query, $onlyActive, $perPage): LengthAwarePaginator {
            $builder = Product::query();

            if ($query !== null && $query !== '') {
                $builder->where('name', 'like', '%'.$query.'%');
            }

            if ($onlyActive === true) {
                $builder->where('is_active', true);
            }

            return $builder->orderBy('name')->paginate($perPage);
        });

        // Ensure paginator links point to the current URL context (Livewire or JSON route)
        return $paginator->withPath(url()->current());
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::query()->where('slug', $slug)->first();
    }

    public function create(array $data): Product
    {
        $name = $data['name'];
        $slugBase = isset($data['slug']) && $data['slug'] !== '' ? $data['slug'] : $name;

        $data['slug'] = $this->generateUniqueSlug($slugBase);

        return Product::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'price' => $data['price'],
            'stock' => $data['stock'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Product $product, array $data): Product
    {
        if (array_key_exists('slug', $data) && $data['slug'] !== null && $data['slug'] !== '') {
            $incoming = Str::slug($data['slug']);
            if ($incoming !== $product->slug) {
                $data['slug'] = $this->generateUniqueSlug($incoming, $product->id);
            } else {
                unset($data['slug']);
            }
        } else {
            unset($data['slug']);
        }

        $product->fill([
            'name' => $data['name'] ?? $product->name,
            'price' => $data['price'] ?? $product->price,
            'stock' => $data['stock'] ?? $product->stock,
            'is_active' => $data['is_active'] ?? $product->is_active,
        ]);

        $product->save();

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function toggleActive(Product $product, ?bool $force = null): Product
    {
        $product->is_active = $force ?? ! $product->is_active;
        $product->save();

        return $product->refresh();
    }

    protected function generateUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $original = $slug;
        $i = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
