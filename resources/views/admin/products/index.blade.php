<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Gerenciar Produtos</h1>
                <p class="text-sm text-gray-600">Administre o catálogo de produtos.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Novo Produto
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                <div class="flex">
                    <svg class="flex-shrink-0 h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        
        <div class="mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input
                    type="text"
                    id="searchProducts"
                    placeholder="Buscar por nome do produto..."
                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm placeholder-gray-400"
                >
                <button
                    type="button"
                    id="clearSearchProducts"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center hidden"
                >
                    <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nome
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Slug
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Preço
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estoque
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr class="product-row hover:bg-gray-50 transition-colors" data-product-name="{{ $product->name }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $product->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($product->stock <= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $product->stock }}
                                        </span>
                                    @elseif($product->stock < 10)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $product->stock }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button 
                                        type="button"
                                        data-product-id="{{ $product->id }}"
                                        data-active="{{ $product->is_active ? 'true' : 'false' }}"
                                        class="toggle-active inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }} hover:opacity-80 cursor-pointer transition-opacity">
                                        {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Nenhum produto cadastrado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="bg-gray-50 px-6 py-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    

    <script>
        (function initAdminProducts() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindHandlers);
            } else {
                bindHandlers();
            }

            function bindHandlers() {
                const $searchInput = $('#searchProducts');
                const $clearButton = $('#clearSearchProducts');

                $searchInput.on('input', function() {
                    const searchTerm = $(this).val().toLowerCase().trim();

                    if (searchTerm) {
                        $clearButton.removeClass('hidden');
                    } else {
                        $clearButton.addClass('hidden');
                    }

                    $('.product-row').each(function() {
                        const $row = $(this);
                        const productName = $row.data('product-name').toLowerCase();

                        if (productName.includes(searchTerm)) {
                            $row.show();
                        } else {
                            $row.hide();
                        }
                    });
                });

                $clearButton.on('click', function() {
                    $searchInput.val('').trigger('input');
                });

                $(document).on('click', '.toggle-active', function() {
                    const $btn = $(this);
                    const productId = $btn.data('product-id');
                    const currentStatus = $btn.data('active') === true || $btn.data('active') === 'true';

                    $btn.prop('disabled', true);

                    $.ajax({
                        url: `/admin/products/${productId}/toggle-active`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        data: JSON.stringify({ force: !currentStatus }),
                        contentType: 'application/json'
                    }).done(function(response) {
                        if (response.ok) {
                            const newStatus = response.data.product.is_active;
                            $btn.data('active', newStatus ? 'true' : 'false');
                            if (newStatus) {
                                $btn.removeClass('bg-gray-100 text-gray-800').addClass('bg-emerald-100 text-emerald-800');
                                $btn.text('Ativo');
                            } else {
                                $btn.removeClass('bg-emerald-100 text-emerald-800').addClass('bg-gray-100 text-gray-800');
                                $btn.text('Inativo');
                            }
                            showToast(response.message || 'Status atualizado com sucesso');
                        } else {
                            showToast(response.error?.message || 'Erro ao atualizar status', 'error');
                        }
                    }).fail(function(xhr) {
                        const msg = window.handleAjaxError(xhr, 'Erro ao atualizar status do produto');
                        showToast(msg, 'error');
                    }).always(function() {
                        $btn.prop('disabled', false);
                    });
                });
            }
        })();
    </script>
</x-app-layout>
