<x-app-layout>
    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Voltar</a>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-start justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
                @if(!$product->is_active)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200">Inativo</span>
                @endif
            </div>

            <div class="mt-4 text-gray-700">
                <div class="text-lg">Preço: <span class="font-medium">R$ {{ number_format($product->price, 2, ',', '.') }}</span></div>
                <div class="text-sm text-gray-500 mt-1">Estoque: {{ $product->stock }}</div>
            </div>

            <div class="mt-6">
                @auth
                    @if(!$product->is_active)
                        <button class="inline-flex items-center px-4 py-2 rounded-md bg-gray-400 text-white text-sm cursor-not-allowed" disabled>Deactivated</button>
                    @elseif($product->stock == 0)
                        <div class="p-4 bg-red-50 rounded-md border border-red-200">
                            <p class="text-sm text-red-600 font-medium">Produto fora de estoque</p>
                        </div>
                    @else
                        <form id="add-to-cart-form" class="flex items-center gap-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}" />
                            <label class="text-sm text-gray-600">Quantidade</label>
                            <input name="quantity" type="number" min="1" step="1" value="1" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 text-sm" />
                            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-black text-white text-sm hover:bg-gray-800 disabled:opacity-50">Adicionar ao carrinho</button>
                        </form>
                    @endif
                @else
                    <div class="p-4 bg-gray-50 rounded-md border border-gray-200">
                        <p class="text-sm text-gray-600">Faça login para adicionar produtos ao carrinho.</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-black text-white text-sm hover:bg-gray-800">Entrar</a>
                            <a href="{{ route('register') }}" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">Registrar</a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    @auth
    
    @endauth
</x-app-layout>


