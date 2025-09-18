# Teste Técnico - Desenvolvedor(a) Full Stack PHP/Laravel

Olá!

Seu desafio é desenvolver uma aplicação em Laravel 12 que permita testar seu domínio em PHP, Laravel, Blade, Livewire e jQuery/AJAX.

## Desafio: Catálogo com Pedidos

### 1. Backend

* **Autenticação padrão** (Laravel Breeze/Jetstream pode usar).
* **Perfis de usuário**: admin e cliente.
* **Entidades**:
  * **Produto**: nome, slug, preço, estoque, ativo.
  * **Pedido**: código (uuid), user_id, total, status: pendente/pago/cancelado.
  * **PedidoItem**: produto_id, quantidade, preço_unitário.
* **Regras**:
  * Slug único para cada produto.
  * O Estoque não pode ficar negativo.
  * Ao confirmar pagamento:
    * Reduz estoque.
    * Envia e-mail de confirmação (simular via Mailtrap ou `Mail::fake` em testes).

### 2. Frontend

#### 2.1. Listagem de Produtos (Blade + Livewire)

* Página pública `/produtos`.
* Deve permitir:
  * Busca por nome (Livewire).
  * Filtro de "somente ativos" (Livewire).
  * Atualização em tempo real sem recarregar a página.

#### 2.2. Detalhe do Produto (Blade + AJAX)

* Página `/produtos/{slug}`.
* Deve exibir:
  * Nome, preço, estoque.
  * O Botão "Adicionar ao Carrinho".
* O botão chama via jQuery AJAX um endpoint `/carrinho/add`, que:
  * Recebe `{produto_id, quantidade}`.
  * Retorna JSON com total atualizado.
* Mostrar no header da página um contador de itens do carrinho atualizado dinamicamente.

#### 2.3. Pedidos

* Cliente pode fechar o carrinho e gerar um pedido.
* Admin pode listar todos os pedidos e alterar status para "pago".

### 3. Regras de Segurança

* **Policies obrigatórias**:
  * Cliente só acessa os próprios pedidos.
  * Admin pode gerenciar produtos e pedidos.
* Validações em **Form Requests**.

### 4. Extras (valorizado)

* Cache de listagem de produtos (60s).
* Jobs/Queues para envio de e-mail de confirmação.
* **Testes Feature**:
  * Criar pedido com sucesso.
  * Estoque atualizado corretamente.
  * Policy restringindo acesso.
* Testes Livewire de busca/filtro.

### 5. Entrega

* Repositório GitHub (público ou privado).
* `README.md` com instruções de setup (migrate-seed, usuários de teste).
* **Usuários seeds**:
  * **Admin**: `admin@example.com` / `password`
  * **Cliente**: `cliente@example.com` / `password`

### Critérios de Avaliação

1. **Organização do código** (Models, Controllers, Requests, Policies, Components).
2. **Uso correto de Livewire** (componentização, busca/filtro).
3. **Uso correto de jQuery/AJAX** (carrinho dinâmico).
4. **Testes básicos implementados**.
5. **Qualidade do banco** (migrations, índices).
6. **Boas práticas** (segurança, tratamento de erros, clean code).
