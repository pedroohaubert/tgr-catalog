<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div class="text-sm text-gray-600">Resultados</div>
        <form wire:submit.prevent="applySearch" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 text-sm">
            <input
                wire:model.debounce.300ms="query"
                type="search"
                placeholder="Buscar..."
                class="block w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 text-sm"
            />
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                    <input type="checkbox" wire:model="showInactive" class="w-3 h-3 rounded border-gray-300 text-gray-800 focus:ring-gray-700" />
                    Inativos
                </label>
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-black text-white text-sm hover:bg-gray-800">Buscar</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($products as $product)
            <a href="{{ route('products.show', $product->slug) }}" class="group block border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition">
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-medium text-gray-900 group-hover:text-gray-700">{{ $product->name }}</h3>
                    @if(!$product->is_active)
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">Inativo</span>
                    @endif
                </div>
                <div class="mt-2 text-sm text-gray-600">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
                <div class="mt-1 text-xs text-gray-500">Estoque: {{ $product->stock }}</div>
            </a>
        @empty
            <div class="col-span-full text-sm text-gray-500">Nenhum produto encontrado.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
