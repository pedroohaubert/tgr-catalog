<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Voltar para produtos</a>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Novo Produto</h1>

            <form id="createProductForm" action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome do Produto *
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name"
                            value="{{ old('name') }}"
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
                            value="{{ old('slug') }}"
                            placeholder="Gerado automaticamente se vazio"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700 @error('slug') border-red-300 @enderror"
                        >
                        <p class="mt-1 text-xs text-gray-500">Deixe vazio para gerar automaticamente baseado no nome</p>
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
                                value="{{ old('price', '0.00') }}"
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
                                value="{{ old('stock', 0) }}"
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
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-gray-800 focus:ring-gray-700 mr-2"
                            >
                            <span class="text-sm text-gray-700">Produto ativo</span>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-black text-white text-sm hover:bg-gray-800">
                            Criar Produto
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let slugManuallyChanged = false;
            
            $('#slug').on('input', function() {
                slugManuallyChanged = $(this).val().length > 0;
            });
            
            $('#name').on('input', function() {
                if (!slugManuallyChanged) {
                    const slug = $(this).val()
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim();
                    $('#slug').val(slug);
                }
            });

            $('#createProductForm').on('submit', function(e) {
                const $form = $(this);
                const $btn = $form.find('button[type=submit]');
                
                $btn.prop('disabled', true);
                $btn.html('Criando...');
                
                return true;
            });
        });
    </script>
</x-app-layout>
