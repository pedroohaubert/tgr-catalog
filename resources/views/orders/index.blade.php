<x-app-layout>
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Meus Pedidos</h1>
            <p class="text-sm text-gray-600">Acompanhe seus pedidos recentes.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg divide-y">
            @forelse ($paginator as $order)
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-600">Código</div>
                            <div class="font-medium text-gray-900">{{ $order->code }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">Total</div>
                            <div class="font-medium text-gray-900">R$ {{ number_format($order->total, 2, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Status:
                            @if($order->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 ml-1">
                                    Pendente
                                </span>
                            @elseif($order->status === 'paid')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 ml-1">
                                    Pago
                                </span>
                            @elseif($order->status === 'canceled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-1">
                                    Cancelado
                                </span>
                            @else
                                <span class="ml-1 uppercase">{{ $order->status }}</span>
                            @endif
                        </div>
                        @if($order->status === 'pending')
                            <button
                                type="button"
                                data-order-id="{{ $order->id }}"
                                data-order-code="{{ $order->code }}"
                                class="cancel-order-client inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar Pedido
                            </button>
                        @endif
                    </div>

                    <div class="mt-4 text-sm">
                        <div class="font-medium text-gray-800 mb-2">Itens</div>
                        <div class="space-y-1">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <div class="text-gray-700">{{ $item->product?->name ?? 'Produto #'.$item->product_id }}</div>
                                    <div class="text-gray-500">x{{ $item->quantity }} — R$ {{ number_format($item->unit_price, 2, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-sm text-gray-500">Você ainda não possui pedidos.</div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $paginator->links() }}
        </div>
    </div>

    
    <div id="cancelModalClient" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-gray-900/60"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative bg-white rounded-lg max-w-md w-full p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cancelar Pedido</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Tem certeza que deseja cancelar o pedido <strong id="cancelOrderCodeClient"></strong>?
                </p>
                <p class="text-xs text-gray-500 mb-4">
                    Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="closeCancelModalClient()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Voltar
                    </button>
                    <button
                        type="button"
                        id="confirmCancelClient"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Confirmar Cancelamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function initClientOrders() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindHandlers);
            } else {
                bindHandlers();
            }

            function bindHandlers() {
                // Cancel order for client
                let orderToCancel = null;

                $(document).on('click', '.cancel-order-client', function() {
                    orderToCancel = $(this).data('order-id');
                    const orderCode = $(this).data('order-code');
                    $('#cancelOrderCodeClient').text(orderCode);
                    $('#cancelReasonClient').val('');
                    $('#cancelModalClient').removeClass('hidden');
                });

                window.closeCancelModalClient = function() {
                    $('#cancelModalClient').addClass('hidden');
                    orderToCancel = null;
                };

                $('#confirmCancelClient').on('click', function() {
                    if (!orderToCancel) return;

                    const $btn = $(this);
                    const originalText = $btn.html();
                    const $modal = $('#cancelModalClient');
                    const $allButtons = $modal.find('button');
                    const $modalContent = $modal.find('.relative');

                    // Disable all buttons and show loading overlay
                    $allButtons.prop('disabled', true);
                    $btn.html('<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Cancelando...');

                    // Add loading overlay
                    $modalContent.append('<div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg"><div class="flex items-center space-x-2 text-gray-600"><svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Processando...</span></div></div>');

                    $.ajax({
                        url: `/meus-pedidos/${orderToCancel}/cancelar`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    }).done(function(response) {
                        if (response.ok) {
                            // Close modal immediately and show success toast
                            window.closeCancelModalClient();
                            showToast(response.message || 'Pedido cancelado com sucesso', 'success');
                            setTimeout(() => { window.location.reload(); }, 800);
                        } else {
                            showToast(response.error?.message || 'Erro ao cancelar pedido', 'error');
                            setTimeout(window.closeCancelModalClient, 2000);
                        }
                    }).fail(function(xhr) {
                        const msg = window.handleAjaxError(xhr, 'Erro ao cancelar pedido');
                        showToast(msg, 'error');
                        setTimeout(window.closeCancelModalClient, 2000);
                    }).always(function() {
                        $allButtons.prop('disabled', false);
                        $btn.html(originalText);
                        $modalContent.find('.absolute').remove();
                    });
                });

                // Close modal on ESC key
                $(document).on('keyup', function(e) {
                    if (e.key === 'Escape') window.closeCancelModalClient();
                });
            }
        })();
    </script>
</x-app-layout>


