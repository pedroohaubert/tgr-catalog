<x-mail::message>
# Olá {{ $userName }}!

Seu pagamento foi confirmado com sucesso.

- **Pedido ID**: {{ $orderId }}
- **Código**: {{ $orderCode }}
- **Itens**: {{ $itemCount }}
- **Total**: R$ {{ number_format($total, 2, ',', '.') }}

<x-mail::button :url="$ordersUrl">
Ver meus pedidos
</x-mail::button>

Obrigado,
{{ config('app.name') }}
</x-mail::message>
