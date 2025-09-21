## Plano de Execução – Catálogo com Pedidos (Laravel 12)

> Use as checkboxes para acompanhar. A ordem é progressiva: comece pelo backend e fundações (auth, models, regras), depois frontend (Livewire/AJAX), testes e entrega.

### 0) Preparação do repositório e ambiente

- [X] Criar repositório Git (GitHub) e inicializar localmente
  - [X] `git init` no diretório do projeto
  - [X] Criar `.gitignore` (usar padrão Laravel + IDE)
  - [X] Criar branch `main`
- [X] Escolher banco de dados: SQLite
- [X] Preparar PHP e extensões (>= 8.2), Composer
  - [X] Habilitar/extensões: pdo, pdo_mysql ou pdo_pgsql, mbstring, intl, openssl
- [ ] Configurar Mailtrap para dev
  - [ ] Criar inbox e obter credenciais SMTP

### 1) Bootstrap do projeto Laravel 12

- [X] Criar projeto: `composer create-project laravel/laravel:^12 tgr-catalog`
- [X] Entrar no projeto e configurar `.env`
  - [X] `APP_NAME`, `APP_URL`
  - [X] `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - [ ] `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- [X] Subir chaves: `php artisan key:generate`
- [X] Configurar `pint` e scripts Composer (format/check):
  - [X] Adicionar `laravel/pint` como dev
  - [X] Scripts: `format` e `lint`
- [X] Instalar Breeze (ou Jetstream) para autenticação
  - [X] `composer require laravel/breeze --dev`
  - [X] `php artisan breeze:install blade`
  - [X] `php artisan migrate`

### 2) Modelagem e migrations

- [X] Definir entidades e atributos
  - [X] Produto: `name`, `slug` (único), `price` (decimal), `stock` (int >=0), `is_active` (bool)
  - [X] Order: `code` (uuid), `user_id` (fk), `total` (decimal), `status` (enum: pending, paid, canceled)
  - [X] OrderItem: `order_id` (fk), `product_id` (fk), `quantity` (int >0), `unit_price` (decimal)
- [X] Criar migrations com índices/uniques/constraints
  - [X] Índice/unique: `products.slug`
  - [X] Índices: `orders.user_id`, `order_items.order_id`, `order_items.product_id`
  - [X] Constraints: FKs com `cascade on delete` em itens
  - [X] Check constraints (quando suportado) para `quantity > 0`, `stock >= 0`
  - [X] Enum status via `string` + validation, ou `enum` nativo quando suportado
- [X] Models Eloquent com `$fillable` explícito e casts
  - [X] `Product` ($fillable, casts: price decimal:2, is_active bool)
  - [X] `Order` ($fillable, casts: total decimal:2, status string, code uuid)
  - [X] `OrderItem` ($fillable, casts: unit_price decimal:2)
- [X] Factories e seeders mínimos
  - [X] `ProductFactory` com `slug` único
  - [X] Seeder de usuários: Admin e Cliente (dados do desafio)
  - [X] Seeder de produtos exemplo

### 3) Autorização e perfis

- [X] Adicionar coluna `role` em `users` (enum: admin, client)

  - [X] Migration + cast + accessor helper `isAdmin()`
  - [X] Popular via seeder: admin/cliente
- [X] Policies

  - [X] `OrderPolicy`: cliente só acessa os próprios pedidos
  - [X] `ProductPolicy`/`OrderPolicy`: admin pode gerenciar produtos e pedidos
  - [ ] Aplicar via `authorize()` nos controllers/rotas

### 4) Serviços de domínio e regras de negócio (app/Services)

- [X] `ProductService`
  - [X] CRUD de produto com validações e slug único
- [X] `CartService` (em sessão)
  - [X] Adicionar/remover/atualizar itens, calcular total, contar itens
  - [X] Impedir adicionar quantidade superior ao estoque
- [X] `OrderService`
  - [X] Criar pedido a partir do carrinho (transação)
  - [X] Confirmar pagamento (idempotente):
    - [X] Recalcular total e travar linhas de produto (`for update`)
    - [X] Debitar estoque (nunca negativo); conflito => HTTP 409
    - [X] Disparar evento `OrderPaid`

### 5) Validações (Form Requests) e DTOs

- [X] `ProductStoreRequest` / `ProductUpdateRequest`
  - [X] name: required|string; price: required|numeric|min:0; stock: int|min:0; is_active: bool; slug: sometimes|unique
- [X] `CartAddRequest` (AJAX): product_id exists, quantity int|min:1
- [X] `OrderCheckoutRequest`: sem payload (usa sessão) mas `authorize()` cliente autenticado
- [X] `OrderPayRequest` (admin): status target = paid

### 6) Controllers (finos) e rotas

- [ ] Rotas web
  - [ ] Públicas: `/produtos` (listagem Livewire), `/produtos/{slug}` (detalhe)
  - [ ] Carrinho (AJAX): `POST /carrinho/add`, `POST /carrinho/remove`, `POST /carrinho/update`
  - [ ] Checkout: `POST /checkout` (cliente) => cria pedido
  - [ ] Pedidos: `GET /me/pedidos` (cliente), `GET /admin/pedidos`, `POST /admin/pedidos/{order}/pay`
- [ ] Controllers
  - [ ] `ProductController`: detalhe produto
  - [ ] `CartController`: add/remove/update (JSON)
  - [ ] `CheckoutController`: criar pedido
  - [ ] `Admin/OrderController`: listar e pagar pedido

### 7) Eventos, listeners e e-mails

- [ ] Evento `OrderPaid`
- [ ] Listener `SendOrderConfirmation`
  - [ ] Enviar e-mail (Mailtrap) com resumo do pedido
  - [ ] Disparar via queue (job) em produção; em dev, sync ok; em testes `Mail::fake()`
- [ ] Mailable `OrderPaidMail`

### 8) Frontend – Blade + Livewire

- [ ] Componente `ProductsList` (página `/produtos`)
  - [ ] Propriedades: `query`, `onlyActive`
  - [ ] Busca por nome, filtro ativos; paginação; cache 60s da consulta
  - [ ] Atualização em tempo real (Livewire)
- [ ] Página de detalhe `/produtos/{slug}` (Blade)
  - [ ] Botão "Adicionar ao Carrinho"
  - [ ] Header com contador de itens no carrinho (Livewire mini-componente ou AJAX)

### 9) AJAX – Carrinho

- [ ] Incluir CSRF no header das requisições jQuery
- [ ] Endpoints retornam somente JSON:
  - [ ] Sucesso: `{ ok: true, data, message? }`
  - [ ] Erro: `{ ok: false, error: { code, message, details? } }` com status apropriado
- [ ] Fluxos
  - [ ] Add: valida quantidade <= estoque; atualiza sessão; retorna total/contador
  - [ ] Update/remove: idem
  - [ ] Header atualiza contador via resposta JSON

### 10) Segurança

- [ ] Aplicar Policies em controllers e rotas (middleware `can`)
- [ ] CSRF obrigatório em AJAX (419 em inválido)
- [ ] Evitar mass assignment (usar `$fillable` e serviços)
- [ ] Sanitizar/validar todos inputs via Form Requests
- [ ] Não expor campos sensíveis em JSON

### 11) Testes

- [ ] Configurar Pest/PHPUnit
- [ ] Feature: autenticação básica (login)
- [ ] Feature: criar pedido com sucesso (cliente)
- [ ] Feature: estoque atualizado corretamente ao pagar
- [ ] Feature: policy restringe acesso a pedidos de outros usuários
- [ ] Livewire: busca/filtro na listagem de produtos
- [ ] JSON/AJAX: endpoints de carrinho retornam contratos corretos
- [ ] Usar factories/seeders; `Mail::fake()` para e-mail

### 12) Qualidade e ferramentas

- [ ] Rodar migrations e seeders (`php artisan migrate --seed`)
- [ ] Rodar Pint (format)
- [ ] Verificar logs/erros; mapear exceções para HTTP correto

### 13) README e entrega

- [ ] `README.md` com:
  - [ ] Requisitos (PHP 8.2+, Composer, DB, Mailtrap)
  - [ ] Setup passo a passo (clone, `.env`, `migrate --seed`)
  - [ ] Usuários seed: Admin `admin@example.com` / `password`; Cliente `cliente@example.com` / `password`
  - [ ] Comandos úteis e como rodar testes
- [ ] Subir projeto ao GitHub e adicionar revisor

### 14) Extras (valorizado)

- [ ] Cache da listagem de produtos (60s)
- [ ] Jobs/Queues para e-mail de confirmação
- [ ] Observers para geração de slug automática
- [ ] Métricas/Logs de domínio (pagamentos, falhas de estoque)
