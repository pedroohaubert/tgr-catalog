<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function page(Request $request, ProductService $products): JsonResponse
    {
        $paginator = $products->paginate(
            query: null,
            onlyActive: true,
            perPage: (int) $request->query('per_page', 15),
        );

        // FUTURE: This will render the public products Blade page. Livewire will hit the data route.
        // Example: return view('products.index');
        return $this->jsonSuccess($this->paginateResponse($paginator));
    }

    public function index(Request $request, ProductService $products): JsonResponse
    {
        $query = (string) $request->query('q', '');
        $onlyActiveParam = $request->query('only_active');
        $onlyActive = $onlyActiveParam === null ? true : (bool) (int) $onlyActiveParam;

        $paginator = $products->paginate(
            query: $query !== '' ? $query : null,
            onlyActive: $onlyActive,
            perPage: (int) $request->query('per_page', 15),
        );

        // FUTURE: This will render the public products page (Blade/Livewire) and use a JSON data route for Livewire.
        // Example: return view('products.index');
        return $this->jsonSuccess($this->paginateResponse($paginator));
    }

    public function show(string $slug, ProductService $products): JsonResponse
    {
        $product = $products->findBySlug($slug);
        if ($product === null) {
            return $this->jsonError('not_found', 'Produto não encontrado.', null, 404);
        }

        // FUTURE: This will render the public product detail page with AJAX add-to-cart.
        // Example: return view('products.show', ['product' => $product]);
        return $this->jsonSuccess($this->transformProduct($product));
    }

    private function paginateResponse(LengthAwarePaginator $paginator): array
    {
        return [
            'items' => array_map(fn ($p) => $this->transformProduct($p), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    private function transformProduct(object $product): array
    {
        return [
            'id' => (int) $product->id,
            'name' => (string) $product->name,
            'slug' => (string) $product->slug,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'is_active' => (bool) $product->is_active,
            'created_at' => optional($product->created_at)?->toISOString(),
            'updated_at' => optional($product->updated_at)?->toISOString(),
        ];
    }
}
