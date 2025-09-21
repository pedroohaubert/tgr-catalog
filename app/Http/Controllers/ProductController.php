<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function page(Request $request, ProductService $products)
    {
        return view('products.index');
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

        return $this->jsonSuccess($this->paginateResponse($paginator));
    }

    public function show(string $slug, ProductService $products)
    {
        $product = $products->findBySlug($slug);
        if ($product === null) {
            abort(404);
        }

        return view('products.show', ['product' => $product]);
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
            'name' => (string) $product->name,
            'slug' => (string) $product->slug,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'is_active' => (bool) $product->is_active,
        ];
    }
}
