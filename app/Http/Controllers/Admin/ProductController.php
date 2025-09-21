<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductStoreData;
use App\DTOs\ProductUpdateData;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Product\ProductDeleteRequest;
use App\Http\Requests\Product\ProductIndexRequest;
use App\Http\Requests\Product\ProductShowRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductToggleActiveRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ProductController extends ApiController
{
    public function index(ProductIndexRequest $request, ProductService $products): JsonResponse
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

        // FUTURE: This will render the admin products page (Blade) with server-provided props.
        // Example: return view('admin.products.index', ['paginator' => $paginator]);
        return $this->jsonSuccess($this->paginateResponse($paginator));
    }

    public function show(ProductShowRequest $request, Product $product): JsonResponse
    {
        // FUTURE: This will render the admin product detail page with edit controls.
        // Example: return view('admin.products.show', ['product' => $product]);
        return $this->jsonSuccess($this->transformProduct($product));
    }

    public function store(ProductStoreRequest $request, ProductService $products): JsonResponse
    {
        $dto = ProductStoreData::fromRequest($request);
        $created = $products->create($dto->toArray());

        // FUTURE: Return 302 redirect to admin product detail page instead of JSON.
        // Example: return redirect()->route('admin.products.show', $created);
        return $this->jsonSuccess(['product' => $this->transformProduct($created)], 'Produto criado.', 201);
    }

    public function update(ProductUpdateRequest $request, Product $product, ProductService $products): JsonResponse
    {
        $dto = ProductUpdateData::fromRequest($request);
        $updated = $products->update($product, $dto->toArray());

        return $this->jsonSuccess(['product' => $this->transformProduct($updated)], 'Produto atualizado.');
    }

    public function toggleActive(ProductToggleActiveRequest $request, Product $product, ProductService $products): JsonResponse
    {
        $data = $request->validated();
        $force = array_key_exists('force', $data) ? (bool) $data['force'] : null;
        $updated = $products->toggleActive($product, $force);

        return $this->jsonSuccess(['product' => $this->transformProduct($updated)], 'Status atualizado.');
    }

    public function destroy(ProductDeleteRequest $request, Product $product, ProductService $products): JsonResponse
    {
        if ($product->items()->exists()) {
            return $this->jsonError('conflict', 'Produto possui itens de pedido e não pode ser excluído.', null, 409);
        }

        $products->delete($product);

        return $this->jsonSuccess(null, 'Produto removido.');
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
