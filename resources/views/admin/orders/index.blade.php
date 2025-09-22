@php
    use Illuminate\Support\Str;
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Gerenciar Pedidos</h1>
            <p class="text-sm text-gray-600">Visualize e gerencie todos os pedidos dos clientes.</p>
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
                    id="searchOrders"
                    placeholder="Buscar por código do pedido..."
                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm placeholder-gray-400"
                >
                <button
                    type="button"
                    id="clearSearchOrders"
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
                                Código
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Itens
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr class="order-row hover:bg-gray-50 transition-colors" data-order-code="{{ $order->code }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono text-gray-900">
                                        {{ Str::limit($order->code, 13) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button 
                                        type="button"
                                        onclick="toggleItems({{ $order->id }})"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                        {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'item' : 'itens' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">R$ {{ number_format($order->total, 2, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($order->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            <svg class="-ml-0.5 mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                            Pendente
                                        </span>
                                    @elseif($order->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            <svg class="-ml-0.5 mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Pago
                                        </span>
                                    @elseif($order->status === 'canceled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="-ml-0.5 mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Cancelado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    @if($order->status === 'pending')
                                        <div class="flex flex-col space-y-1">
                                            <button
                                                type="button"
                                                data-order-id="{{ $order->id }}"
                                                data-order-code="{{ $order->code }}"
                                                class="pay-order inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Pagar
                                            </button>
                                            <button
                                                type="button"
                                                data-order-id="{{ $order->id }}"
                                                data-order-code="{{ $order->code }}"
                                                class="cancel-order inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Cancelar
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr id="items-{{ $order->id }}" class="order-row order-items-row hidden bg-blue-50 border-b border-blue-100" data-order-code="{{ $order->code }}">
                                <td colspan="7" class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-800 mb-2">Itens do pedido:</div>
                                        <div class="space-y-1">
                                            @foreach ($order->items as $item)
                                                <div class="flex items-center justify-between text-gray-600">
                                                    <div>
                                                        {{ $item->product?->name ?? 'Produto #'.$item->product_id }}
                                                        <span class="text-gray-400 mx-2">×</span>
                                                        {{ $item->quantity }} unidade{{ $item->quantity > 1 ? 's' : '' }}
                                                    </div>
                                                    <div>
                                                        R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Nenhum pedido encontrado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="bg-gray-50 px-6 py-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    
    <div id="payModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-gray-900/60"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative bg-white rounded-lg max-w-sm w-full p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar Pagamento</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Confirmar pagamento do pedido <strong id="payOrderCode"></strong>?
                </p>
                <p class="text-xs text-gray-500 mb-4">
                    Esta ação irá marcar o pedido como pago, deduzir o estoque dos produtos e enviar um e-mail de confirmação ao cliente.
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closePayModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button 
                        type="button"
                        id="confirmPay"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        Confirmar Pagamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div id="cancelModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-gray-900/60"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative bg-white rounded-lg max-w-md w-full p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cancelar Pedido</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Cancelar o pedido <strong id="cancelOrderCode"></strong>?
                </p>
                <p class="text-xs text-gray-500 mb-4">
                    Esta ação irá marcar o pedido como cancelado. O estoque não será afetado.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeCancelModal()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Voltar
                    </button>
                    <button
                        type="button"
                        id="confirmCancel"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Confirmar Cancelamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Ensure all bindings happen after DOM and Vite JS are ready
        (function initAdminOrders() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindHandlers);
            } else {
                bindHandlers();
            }

            function bindHandlers() {
                // Search functionality
                const $searchInput = $('#searchOrders');
                const $clearButton = $('#clearSearchOrders');

                $searchInput.on('input', function() {
                    const searchTerm = $(this).val().toLowerCase().trim();

                    if (searchTerm) {
                        $clearButton.removeClass('hidden');
                    } else {
                        $clearButton.addClass('hidden');
                    }

                    // Primeiro, esconder todas as linhas de itens que estão expandidas
                    $('.order-items-row').addClass('hidden');

                    // Filtrar apenas as linhas principais dos pedidos
                    $('.order-row:not(.order-items-row)').each(function() {
                        const $row = $(this);
                        const orderCode = $row.data('order-code').toLowerCase();

                        if (orderCode.includes(searchTerm)) {
                            $row.show();
                        } else {
                            $row.hide();
                        }
                    });
                });

                $clearButton.on('click', function() {
                    $searchInput.val('').trigger('input');
                });

                // Toggle items visibility
                window.toggleItems = function(orderId) {
                    $('#items-' + orderId).toggleClass('hidden');
                };

                // Pay order
                let orderToPay = null;

                $(document).on('click', '.pay-order', function() {
                    orderToPay = $(this).data('order-id');
                    const orderCode = $(this).data('order-code');
                    $('#payOrderCode').text(orderCode);
                    $('#payModal').removeClass('hidden');
                });

                window.closePayModal = function() {
                    $('#payModal').addClass('hidden');
                    orderToPay = null;
                };

                $('#confirmPay').on('click', function() {
                    if (!orderToPay) return;

                    const $btn = $(this);
                    const originalText = $btn.html();
                    const $modal = $('#payModal');
                    const $allButtons = $modal.find('button');
                    const $modalContent = $modal.find('.relative');

                    // Disable all buttons and show loading overlay
                    $allButtons.prop('disabled', true);
                    $btn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processando...');

                    // Add loading overlay
                    $modalContent.append('<div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg"><div class="flex items-center space-x-2 text-gray-600"><svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Processando...</span></div></div>');

                    $.ajax({
                        url: `/admin/orders/${orderToPay}/pay`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    }).done(function(response) {
                        if (response.ok) {
                            // Close modal immediately and show success toast
                            window.closePayModal();
                            showToast(response.message || 'Pagamento confirmado com sucesso', 'success');
                            setTimeout(() => { window.location.reload(); }, 800);
                        } else {
                            showToast(response.error?.message || 'Erro ao confirmar pagamento', 'error');
                            setTimeout(window.closePayModal, 2000);
                        }
                    }).fail(function(xhr) {
                        const msg = window.handleAjaxError(xhr, 'Erro ao confirmar pagamento');
                        showToast(msg, 'error');
                        setTimeout(window.closePayModal, 2000);
                    }).always(function() {
                        $allButtons.prop('disabled', false);
                        $btn.html(originalText);
                        $modalContent.find('.absolute').remove();
                    });
                });

                // Cancel order
                let orderToCancel = null;

                $(document).on('click', '.cancel-order', function() {
                    orderToCancel = $(this).data('order-id');
                    const orderCode = $(this).data('order-code');
                    $('#cancelOrderCode').text(orderCode);
                    $('#cancelReason').val('');
                    $('#cancelModal').removeClass('hidden');
                });

                window.closeCancelModal = function() {
                    $('#cancelModal').addClass('hidden');
                    orderToCancel = null;
                };

                $('#confirmCancel').on('click', function() {
                    if (!orderToCancel) return;

                    const $btn = $(this);
                    const originalText = $btn.html();
                    const $modal = $('#cancelModal');
                    const $allButtons = $modal.find('button');
                    const $modalContent = $modal.find('.relative');

                    // Disable all buttons and show loading overlay
                    $allButtons.prop('disabled', true);
                    $btn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Cancelando...');

                    // Add loading overlay
                    $modalContent.append('<div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg"><div class="flex items-center space-x-2 text-gray-600"><svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Processando...</span></div></div>');

                    $.ajax({
                        url: `/admin/orders/${orderToCancel}/cancel`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    }).done(function(response) {
                        if (response.ok) {
                            // Close modal immediately and show success toast
                            window.closeCancelModal();
                            showToast(response.message || 'Pedido cancelado com sucesso', 'success');
                            setTimeout(() => { window.location.reload(); }, 800);
                        } else {
                            showToast(response.error?.message || 'Erro ao cancelar pedido', 'error');
                            setTimeout(window.closeCancelModal, 2000);
                        }
                    }).fail(function(xhr) {
                        const msg = window.handleAjaxError(xhr, 'Erro ao cancelar pedido');
                        showToast(msg, 'error');
                        setTimeout(window.closeCancelModal, 2000);
                    }).always(function() {
                        $allButtons.prop('disabled', false);
                        $btn.html(originalText);
                        $modalContent.find('.absolute').remove();
                    });
                });

                // Close modals on ESC key
                $(document).on('keyup', function(e) {
                    if (e.key === 'Escape') {
                        window.closePayModal();
                        window.closeCancelModal();
                    }
                });
            }
        })();
    </script>
</x-app-layout>
