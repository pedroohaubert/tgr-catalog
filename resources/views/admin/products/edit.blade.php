<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Voltar para produtos</a>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Editar Produto</h1>

            <form id="editProductForm" action="{{ route('admin.products.update', $product) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome do Produto *
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            value="{{ old('name', $product->name) }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 @error('name') border-red-300 @enderror" 
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                            Slug (URL)
                        </label>
                        <input 
                            type="text" 
                            name="slug" 
                            id="slug"
                            value="{{ old('slug', $product->slug) }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 @error('slug') border-red-300 @enderror"
                        >
                        <p class="mt-1 text-xs text-gray-500">URL amigável do produto</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Preço (R$) *
                            </label>
                            <input 
                                type="number" 
                                name="price" 
                                id="price"
                                value="{{ old('price', $product->price) }}"
                                step="0.01"
                                min="0"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 @error('price') border-red-300 @enderror" 
                                required
                            >
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
                                Estoque *
                            </label>
                            <input 
                                type="number" 
                                name="stock" 
                                id="stock"
                                value="{{ old('stock', $product->stock) }}"
                                min="0"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 @error('stock') border-red-300 @enderror" 
                                required
                            >
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-gray-800 focus:ring-gray-700 mr-2"
                            >
                            <span class="text-sm text-gray-700">Produto ativo</span>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-6 border-t">
                        <div class="text-xs text-gray-500 mb-4">
                            <div>Criado em: {{ $product->created_at->format('d/m/Y H:i') }}</div>
                            <div>Última atualização: {{ $product->updated_at->format('d/m/Y H:i') }}</div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button 
                                type="button" 
                                id="deleteBtn"
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ $product->name }}"
                                class="text-red-600 hover:text-red-900 text-sm">
                                Excluir produto
                            </button>

                            <div class="flex items-center space-x-3">
                                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Cancelar
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-black text-white text-sm hover:bg-gray-800">
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-gray-500 opacity-75"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar Exclusão</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Tem certeza que deseja excluir o produto "<span id="deleteProductName"></span>"? Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeDeleteModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button 
                        type="button"
                        id="confirmDelete"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Handle form submission
            $('#editProductForm').on('submit', function(e) {
                const $form = $(this);
                const $btn = $form.find('button[type=submit]');
                
                // Visual feedback
                $btn.prop('disabled', true);
                $btn.html('Salvando...');
                
                return true; // Allow normal form submission
            });

            // Delete product
            $('#deleteBtn').on('click', function() {
                const productName = $(this).data('product-name');
                $('#deleteProductName').text(productName);
                $('#deleteModal').removeClass('hidden');
            });

            $('#confirmDelete').on('click', function() {
                const productId = $('#deleteBtn').data('product-id');
                const $btn = $(this);
                const $modal = $('#deleteModal');
                const $allButtons = $modal.find('button');

                // Disable all buttons in the modal
                $allButtons.prop('disabled', true);

                $.ajax({
                    url: `/admin/products/${productId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function(response) {
                    if (response.ok) {
                        showToast(response.message || 'Produto excluído com sucesso');
                        setTimeout(() => {
                            window.location.href = '{{ route('admin.products.index') }}';
                        }, 1500);
                    } else {
                        showToast(response.error?.message || 'Erro ao excluir produto', 'error');
                        closeDeleteModal();
                    }
                }).fail(function(xhr) {
                    const msg = xhr.responseJSON?.error?.message || 'Erro ao excluir produto';
                    showToast(msg, 'error');
                    closeDeleteModal();
                }).always(function() {
                    $allButtons.prop('disabled', false);
                });
            });
        });

        function closeDeleteModal() {
            $('#deleteModal').addClass('hidden');
        }

        // Close modal on ESC key
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                closeDeleteModal();
            }
        });
    </script>
</x-app-layout>
