<nav x-data="{ open: false, query: new URLSearchParams(window.location.search).get('q') || '', onlyActive: (new URLSearchParams(window.location.search).get('only_active') ?? '1') === '1' }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex w-full">
                <!-- Logo / Brand -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('products.index') }}" class="text-xl font-semibold tracking-tight text-gray-900">
                        TGR
                    </a>
                </div>

                <!-- Search -->
                <div class="hidden sm:flex sm:items-center sm:ms-8 sm:flex-1 sm:justify-center">
                    <livewire:header-search />
                </div>
            </div>

            <!-- Right side: Cart + Settings -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                <!-- Cart Dropdown (only for authenticated users) -->
                <div x-data="{
                    open: false,
                    summary: null,
                    loading: false,
                    showClearModal: false,
                    toggle() {
                        this.open = !this.open;
                        if(this.open) {
                            this.fetch();
                        }
                    },
                    async fetch() {
                        this.loading = true;
                        try {
                            const r = await fetch('{{ route('cart.summary') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            const json = await r.json();
                            if (json.ok) {
                                this.summary = json;
                            }
                        } catch(e) {
                            console.error('Cart fetch error:', e);
                        } finally {
                            this.loading = false;
                        }
                    },
                    confirmClear() {
                        this.showClearModal = true;
                    },
                    cancelClear() {
                        this.showClearModal = false;
                    },
                    async executeClear() {
                        this.showClearModal = false;
                        // Use the global cart helpers
                        const key = 'clear';
                        if (window.cartHelpers.inFlight[key]) {
                            return;
                        }
                        window.cartHelpers.inFlight[key] = true;
                        $('.cart-clear').prop('disabled', true);

                        try {
                            const response = await window.cartHelpers.post(window.cartConfig.routes.clear, {});
                            if (response.ok && response.data) {
                                window.cartHelpers.updateUI(response.data);
                            }
                        } finally {
                            delete window.cartHelpers.inFlight[key];
                            $('.cart-clear').prop('disabled', false);
                        }
                    },
                    init() {
                        // Load cart on page load
                        this.fetch();
                        // Listen for cart updates and open
                        window.addEventListener('cart:updated', (e) => {
                            if (e.detail) {
                                this.summary = { ok: true, data: e.detail };
                            }
                        });
                        window.addEventListener('cart:open', () => {
                            this.open = true;
                            this.fetch();
                        });
                    }
                }" class="relative me-4" x-init="init()" data-cart-dropdown>
                    <button x-on:click="toggle()" class="relative inline-flex items-center p-2 rounded-md text-gray-600 hover:text-gray-800">
                        <!-- cart icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.085.836l.383 1.437M7.5 14.25h8.784a1.5 1.5 0 001.447-1.106l1.812-6.652a.75.75 0 00-.722-.942H5.104M7.5 14.25L5.104 5.25M7.5 14.25l-.597 2.389A1.5 1.5 0 008.361 18.75h7.278m0 0a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm-7.278 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        </svg>
                        <span id="cart-count-badge" x-show="summary?.data?.count > 0" x-text="summary?.data?.count" class="absolute -top-1 -right-1 text-[10px] bg-black text-white rounded-full px-1 min-w-[16px] text-center"></span>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open=false" class="absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-md shadow-lg z-50 p-3" data-orders-url="{{ route('orders.index') }}">
                        <template x-if="loading">
                            <div class="text-sm text-gray-500">Carregando...</div>
                        </template>
                        <template x-if="!loading">
                            <div>
                                <template x-if="!summary?.data">
                                    <div class="text-sm text-gray-500">Indisponível.</div>
                                </template>
                                <template x-if="summary?.data && (!summary.data.items || summary.data.items.length === 0)">
                                    <div class="text-sm text-gray-500 py-4 text-center">Carrinho vazio.</div>
                                </template>
                                <template x-if="summary?.data && summary.data.items && summary.data.items.length > 0">
                                    <div>
                                        <div class="max-h-64 overflow-auto">
                                            <template x-for="(item, index) in summary.data.items" :key="item.product_id">
                                                <div class="py-2 flex items-start justify-between text-sm" :class="{ 'border-b border-gray-200': index < summary.data.items.length - 1 }">
                                                    <div class="pr-3">
                                                        <div class="font-medium text-gray-900" x-text="item.name"></div>
                                                        <div class="text-gray-500">R$ <span x-text="item.unit_price.toFixed(2).replace('.', ',')"></span></div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button class="cart-qty p-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50" data-action="decrease" :data-product-id="item.product_id" title="Diminuir">-</button>
                                                        <span class="text-gray-700 min-w-[1.5rem] text-center" x-text="item.quantity"></span>
                                                        <button class="cart-qty p-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50" data-action="increase" :data-product-id="item.product_id" title="Aumentar">+</button>
                                                        <button class="cart-remove text-gray-500 hover:text-red-600" :data-product-id="item.product_id" title="Remover">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center justify-between mt-3 text-sm">
                                            <span class="text-gray-600">Total</span>
                                            <span class="font-semibold" x-text="'R$ ' + Number(summary.data.total).toFixed(2).replace('.', ',')"></span>
                                        </div>
                                        <div class="mt-3 flex items-center gap-2">
                                            <button class="cart-clear flex-1 inline-flex justify-center items-center px-3 py-2 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50" x-on:click="confirmClear()">Limpar</button>
                                            <button class="cart-checkout flex-1 inline-flex justify-center items-center px-3 py-2 rounded-md bg-black text-white text-sm hover:bg-gray-800">Fechar pedido</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Clear Cart Confirmation Modal -->
                    <div x-show="showClearModal" class="fixed inset-0 px-4 py-6 sm:px-0 z-50 flex items-center justify-center" style="display: none;" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <div class="fixed inset-0 bg-gray-500 opacity-75" x-on:click="cancelClear()"></div>
                        <div class="relative mx-auto max-w-sm bg-white rounded-lg overflow-hidden shadow-xl">
                            <div class="p-6">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900">Limpar carrinho</h3>
                            </div>

                            <div class="mt-4">
                                <p class="text-sm text-gray-600">
                                    Tem certeza que deseja remover todos os itens do carrinho? Esta ação não pode ser desfeita.
                                </p>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button x-on:click="cancelClear()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancelar
                                </button>
                                <button x-on:click="executeClear()" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Limpar carrinho
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 me-4">Entrar</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-gray-900 me-4">Registrar</a>
                    @endif
                @endguest

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name ?? 'Conta' }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @auth
                            <x-dropdown-link :href="route('orders.index')">
                                {{ __('Meus Pedidos') }}
                            </x-dropdown-link>
                            
                            @if(Auth::user()->isAdmin())
                                <div class="border-t border-gray-200 my-1"></div>
                                <x-dropdown-link :href="route('admin.products.index')">
                                    {{ __('Gerenciar Produtos') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.orders.index')">
                                    {{ __('Gerenciar Pedidos') }}
                                </x-dropdown-link>
                                <div class="border-t border-gray-200 my-1"></div>
                            @endif
                            
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        @else
                            <x-dropdown-link :href="route('login')">
                                {{ __('Login') }}
                            </x-dropdown-link>
                            @if (Route::has('register'))
                                <x-dropdown-link :href="route('register')">
                                    {{ __('Register') }}
                                </x-dropdown-link>
                            @endif
                        @endauth
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')">
                {{ __('Produtos') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            @auth
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('orders.index')">
                    {{ __('Meus Pedidos') }}
                </x-responsive-nav-link>
                
                @if(Auth::user()->isAdmin())
                    <div class="border-t border-gray-200 my-2"></div>
                    <x-responsive-nav-link :href="route('admin.products.index')">
                        {{ __('Gerenciar Produtos') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.orders.index')">
                        {{ __('Gerenciar Pedidos') }}
                    </x-responsive-nav-link>
                    <div class="border-t border-gray-200 my-2"></div>
                @endif
                
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
            @else
            <div class="px-4">
                <x-responsive-nav-link :href="route('login')">
                    {{ __('Login') }}
                </x-responsive-nav-link>
                @if (Route::has('register'))
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                @endif
            </div>
            @endauth
        </div>
    </div>
</nav>
