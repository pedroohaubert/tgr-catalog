<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductStoreData;
use App\DTOs\ProductUpdateData;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Product\ProductIndexRequest;
use App\Http\Requests\Product\ProductShowRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductToggleActiveRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductController extends ApiController
{
    use AuthorizesRequests;

    public function index(ProductIndexRequest $request, ProductService $products)
    {
        $data = $request->validated();
        $query = (string) ($data['q'] ?? '');
        $onlyActive = array_key_exists('only_active', $data) ? (bool) $data['only_active'] : null;
        $perPage = (int) ($data['per_page'] ?? 15);

        $paginator = $products->paginate(
            query: $query !== '' ? $query : null,
            onlyActive: $onlyActive,
            perPage: $perPage,
        );

        if ($request->expectsJson()) {
            return $this->jsonSuccess($this->paginateResponse($paginator));
        }

        return view('admin.products.index', ['products' => $paginator]);
    }

    public function show(ProductShowRequest $request, Product $product)
    {

        return redirect()->route('admin.products.edit', $product);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('admin.products.edit', compact('product'));
    }

    public function store(ProductStoreRequest $request, ProductService $products): JsonResponse|RedirectResponse
    {
        $dto = ProductStoreData::fromRequest($request);
        $created = $products->create($dto->toArray());

        if ($request->expectsJson() === false) {
            return redirect()->route('admin.products.index')->with('status', 'Produto criado.');
        }

        return $this->jsonSuccess(['product' => $this->transformProduct($created)], 'Produto criado.', 201);
    }

    public function update(ProductUpdateRequest $request, Product $product, ProductService $products)
    {
        $dto = ProductUpdateData::fromRequest($request);
        $updated = $products->update($product, $dto->toArray());

        if ($request->expectsJson()) {
            return $this->jsonSuccess(['product' => $this->transformProduct($updated)], 'Produto atualizado.');
        }

        return redirect()->route('admin.products.index')->with('status', 'Produto atualizado com sucesso.');
    }

    public function toggleActive(ProductToggleActiveRequest $request, Product $product, ProductService $products): JsonResponse
    {
        $data = $request->validated();
        $force = array_key_exists('force', $data) ? (bool) $data['force'] : null;
        $updated = $products->toggleActive($product, $force);

        return $this->jsonSuccess(['product' => $this->transformProduct($updated)], 'Status atualizado.');
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
