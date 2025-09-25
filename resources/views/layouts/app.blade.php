<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        
        <script>
            let toastTimeout;
            function showToast(message, type = 'success') {
                hideToast();

                const toast = document.createElement('div');
                toast.id = 'cart-toast';
                toast.className = `fixed bottom-4 right-4 z-[2000] p-4 rounded-md shadow-lg transition-all duration-300 transform translate-y-[100%] ${
                    type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                }`;
                toast.textContent = message;

                document.body.appendChild(toast);

                
                setTimeout(() => {
                    toast.classList.remove('translate-y-[100%]');
                }, 10);

               
                toastTimeout = setTimeout(() => {
                    hideToast();
                }, 3000);

                toast.addEventListener('click', hideToast);
            }

            function hideToast() {
                clearTimeout(toastTimeout);
                const toast = document.getElementById('cart-toast');
                if (toast) {
                    toast.classList.add('translate-y-[100%]');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }
            }

            window.handleAjaxError = function(xhr, defaultMessage = 'Erro ao processar requisição') {
                let msg = defaultMessage;
                
                if (xhr.status === 401) {
                    msg = 'Sessão expirada. Por favor, faça login novamente';
                    setTimeout(() => window.location.href = '/login', 2000);
                } else if (xhr.status === 403) {
                    msg = 'Sem permissão para realizar esta ação';
                } else if (xhr.status === 404) {
                    msg = 'Recurso não encontrado';
                } else if (xhr.status === 409) {
                    if (xhr.responseJSON?.error?.details) {
                        const details = xhr.responseJSON.error.details;
                        if (details.insufficient_stock && Array.isArray(details.insufficient_stock)) {
                            const products = details.insufficient_stock.map(p => 
                                `${p.product} (disponível: ${p.available}, necessário: ${p.required})`
                            ).join(', ');
                            msg = `Estoque insuficiente: ${products}`;
                        } else {
                            msg = xhr.responseJSON.error.message || defaultMessage;
                        }
                    } else {
                        msg = xhr.responseJSON?.error?.message || 'Conflito ao processar requisição';
                    }
                } else if (xhr.status === 419) {
                    msg = 'Token CSRF inválido. Por favor, recarregue a página';
                    setTimeout(() => window.location.reload(), 2000);
                } else if (xhr.status === 422) {
                    if (xhr.responseJSON?.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        msg = errors.join('. ') || 'Dados inválidos';
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    } else {
                        msg = 'Dados inválidos. Verifique as informações e tente novamente';
                    }
                } else if (xhr.status === 429) {
                    msg = 'Muitas requisições. Por favor, aguarde um momento';
                } else if (xhr.status === 500) {
                    msg = 'Erro interno do servidor. Por favor, tente novamente';
                } else if (xhr.status === 503) {
                    msg = 'Serviço temporariamente indisponível. Tente novamente em alguns instantes';
                } else if (xhr.responseJSON?.error?.message) {
                    msg = xhr.responseJSON.error.message;
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                
                return msg;
            };
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            
            <main>
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts

        
        <script>
            /**
             * Inicializa o sistema de carrinho de compras
             * Configura rotas, estado global e helpers para manipulação do carrinho
             */
            window.initCart = function() {
                /**
                 * Configuração das rotas do backend e token CSRF
                 * Centraliza todas as URLs necessárias para operações do carrinho
                 */
                window.cartConfig = {
                    routes: {
                        summary: '{{ route('cart.summary') }}',
                        add: '{{ route('cart.add') }}',
                        update: '{{ route('cart.update') }}',
                        remove: '{{ route('cart.remove') }}',
                        clear: '{{ route('cart.clear') }}',
                        checkout: '{{ route('checkout.store') }}'
                    },
                    csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };

                /**
                 * Estado global do carrinho mantido em memória
                 * Atualizado sempre que há mudanças no backend
                 */
                window.cartState = {
                    count: 0,
                    itemsById: {}
                };

                /**
                 * Funções auxiliares para manipulação do carrinho
                 * Contém métodos HTTP, atualização de estado e controle de concorrência
                 */
                window.cartHelpers = {
                    /**
                     * Controle de concorrência - evita múltiplas requisições simultâneas
                     * Chaves: 'p{productId}' para quantidade, 'p{productId}:rm' para remoção
                     */
                    inFlight: {},

                    /**
                     * Faz requisições POST com CSRF token e headers apropriados
                     */
                    post: function(url, data) {
                        return $.ajax({
                            url: url,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': window.cartConfig.csrfToken,
                                'Accept': 'application/json'
                            },
                            contentType: 'application/json',
                            data: JSON.stringify(data)
                        });
                    },

                    /**
                     * Faz requisições GET para buscar dados do servidor
                     */
                    get: function(url) {
                        return $.ajax({
                            url: url,
                            type: 'GET',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                    },

                    /**
                     * Atualiza o estado global do carrinho com dados do servidor
                     */
                    setState: function(data) {
                        window.cartState.count = data?.count ?? 0;
                        window.cartState.itemsById = {};
                        if (Array.isArray(data?.items)) {
                            data.items.forEach(function(item) {
                                window.cartState.itemsById[item.product_id] = item;
                            });
                        }
                    },

                    /**
                     * Atualiza o contador de itens no ícone do carrinho
                     */
                    updateBadge: function(count) {
                        const $badge = $('#cart-count-badge');
                        if ($badge.length) {
                            $badge.text(count);
                            $badge.toggle(count > 0);
                        }
                    },

                    /**
                     * Atualiza toda a interface do carrinho após mudanças
                     * Estado global + badge + dispara eventos para outros componentes
                     */
                    updateUI: function(data) {
                        this.setState(data);
                        this.updateBadge(window.cartState.count);

                        window.dispatchEvent(new CustomEvent('cart:updated', { detail: data }));
                    },

                    /**
                     * Busca o estado atual do carrinho do servidor
                     * Chamado na inicialização para sincronizar com backend
                     */
                    fetchSummary: function() {
                        return this.get(window.cartConfig.routes.summary).done((response) => {
                            if (response?.ok && response.data) {
                                this.updateUI(response.data);
                            }
                        });
                    }
                };

                /**
                 * Configura event listeners para interação do usuário com o carrinho
                 * Vincula ações dos botões aos métodos apropriados dos helpers
                 */
                $(document).ready(function() {
                    // Sincroniza estado inicial do carrinho com o servidor
                    window.cartHelpers.fetchSummary();

                    /**
                     * Event listener para formulário de adicionar produto ao carrinho
                     * Processa submit, faz requisição e atualiza UI
                     */
                    $(document).on('submit', '#add-to-cart-form', function(e) {
                        e.preventDefault();
                        const $form = $(this);
                        const $btn = $form.find('button[type=submit]');
                        const $feedback = $('#add-to-cart-feedback');

                        $btn.prop('disabled', true);
                        if ($feedback.length) {
                            $feedback.removeClass('text-red-600 text-green-700').text('');
                        }
                        hideToast();

                        window.cartHelpers.post(window.cartConfig.routes.add, {
                            product_id: parseInt($form.find('[name=product_id]').val(), 10),
                            quantity: parseInt($form.find('[name=quantity]').val(), 10)
                        }).done(function(response) {
                            if (response.ok && response.data) {
                                showToast('Produto adicionado ao carrinho!');
                                $form.find('[name=quantity]').val(1);
                                window.cartHelpers.updateUI(response.data);
                                window.dispatchEvent(new CustomEvent('cart:open'));
                            } else {
                                showToast(response.error?.message || 'Erro ao adicionar', 'error');
                            }
                        }).fail(function(xhr) {
                            const msg = window.handleAjaxError(xhr, 'Erro ao adicionar ao carrinho');
                            showToast(msg, 'error');
                        }).always(function() {
                            $btn.prop('disabled', false);
                        });
                    });

                    /**
                     * Event listener para botões de alterar quantidade (+/-)
                     * Controla concorrência para evitar múltiplas requisições simultâneas
                     */
                    $(document).on('click', '.cart-qty', function(e) {
                        e.preventDefault();
                        const $btn = $(this);
                        const productId = parseInt($btn.data('product-id'), 10);
                        const action = $btn.data('action');

                        const key = 'p' + productId;
                        if (window.cartHelpers.inFlight[key]) {
                            return;
                        }
                        window.cartHelpers.inFlight[key] = true;
                        $('.cart-qty[data-product-id="' + productId + '"]').prop('disabled', true);

                        const currentFromState = window.cartState.itemsById[productId]?.quantity;
                        const $qty = $btn.siblings('span');
                        const currentFromDom = parseInt($qty.text(), 10) || 1;
                        const current = Number.isInteger(currentFromState) ? currentFromState : currentFromDom;
                        const newQty = action === 'increase' ? current + 1 : Math.max(0, current - 1);

                        window.cartHelpers.post(window.cartConfig.routes.update, {
                            product_id: productId,
                            quantity: newQty
                        }).done(function(response) {
                            if (response.ok && response.data) {
                                window.cartHelpers.updateUI(response.data);
                            }
                        }).fail(function(xhr) {
                            const msg = window.handleAjaxError(xhr, 'Erro ao atualizar quantidade');
                            showToast(msg, 'error');
                        }).always(function() {
                            delete window.cartHelpers.inFlight[key];
                            $('.cart-qty[data-product-id="' + productId + '"]').prop('disabled', false);
                        });
                    });

                    /**
                     * Event listener para botões de remover item do carrinho
                     * Usa chave única para controle de concorrência com remoções
                     */
                    $(document).on('click', '.cart-remove', function(e) {
                        e.preventDefault();
                        const productId = parseInt($(this).data('product-id'), 10);

                        const key = 'p' + productId + ':rm';
                        if (window.cartHelpers.inFlight[key]) {
                            return;
                        }
                        window.cartHelpers.inFlight[key] = true;
                        $('.cart-qty[data-product-id="' + productId + '"]').prop('disabled', true);
                        $('.cart-remove[data-product-id="' + productId + '"]').prop('disabled', true);

                        window.cartHelpers.post(window.cartConfig.routes.remove, {
                            product_id: productId
                        }).done(function(response) {
                            if (response.ok && response.data) {
                                window.cartHelpers.updateUI(response.data);
                            }
                        }).fail(function(xhr) {
                            const msg = window.handleAjaxError(xhr, 'Erro ao remover item');
                            showToast(msg, 'error');
                        }).always(function() {
                            delete window.cartHelpers.inFlight[key];
                            $('.cart-qty[data-product-id="' + productId + '"]').prop('disabled', false);
                            $('.cart-remove[data-product-id="' + productId + '"]').prop('disabled', false);
                        });
                    });

                    /**
                     * Event listener para botão de finalizar pedido (checkout)
                     * Processa pedido e redireciona para página de pedidos
                     */
                    $(document).on('click', '.cart-checkout', function(e) {
                        e.preventDefault();

                        window.cartHelpers.post(window.cartConfig.routes.checkout, {})
                            .done(function(response) {
                                if (response.ok) {
                                    window.location.href = '/meus-pedidos';
                                } else {
                                    alert(response.error?.message || 'Erro ao processar pedido');
                                }
                            });
                    });
                });
            };
        </script>
    </body>
</html>
