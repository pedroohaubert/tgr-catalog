## Plano de Execução – Catálogo com Pedidos (Laravel 12)

> Use as checkboxes para acompanhar. A ordem é progressiva: comece pelo backend e fundações (auth, models, regras), depois frontend (Livewire/AJAX), testes e entrega.

### 0) Preparação do repositório e ambiente

- [ ] Criar repositório Git (GitHub) e inicializar localmente
  - [ ] `git init` no diretório do projeto
  - [ ] Criar `.gitignore` (usar padrão Laravel + IDE)
  - [ ] Criar branch `main`
- [ ] Escolher banco de dados: PostgreSQL (recomendado) ou MySQL
  - [ ] Criar database `tgr_catalog` e usuário com permissões
  - [ ] Definir fuso horário, collation/charset (UTF-8)
- [ ] Preparar PHP e extensões (>= 8.2), Composer
  - [ ] Habilitar/extensões: pdo, pdo_mysql ou pdo_pgsql, mbstring, intl, openssl
- [ ] Configurar Mailtrap para dev
  - [ ] Criar inbox e obter credenciais SMTP

### 1) Bootstrap do projeto Laravel 12

- [ ] Criar projeto: `composer create-project laravel/laravel:^12 tgr-catalog`
- [ ] Entrar no projeto e configurar `.env`
  - [ ] `APP_NAME`, `APP_URL`
  - [ ] `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - [ ] `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
- [ ] Subir chaves: `php artisan key:generate`
- [ ] Configurar `pint` e scripts Composer (format/check):
  - [ ] Adicionar `laravel/pint` como dev
  - [ ] Scripts: `format` e `lint`
- [ ] Instalar Breeze (ou Jetstream) para autenticação
  - [ ] `composer require laravel/breeze --dev`
  - [ ] `php artisan breeze:install blade`
  - [ ] `php artisan migrate`

### 2) Modelagem e migrations

- [ ] Definir entidades e atributos
  - [ ] Produto: `name`, `slug` (único), `price` (decimal), `stock` (int >=0), `is_active` (bool)
  - [ ] Order: `code` (uuid), `user_id` (fk), `total` (decimal), `status` (enum: pending, paid, canceled)
  - [ ] OrderItem: `order_id` (fk), `product_id` (fk), `quantity` (int >0), `unit_price` (decimal)
- [ ] Criar migrations com índices/uniques/constraints
  - [ ] Índice/unique: `products.slug`
  - [ ] Índices: `orders.user_id`, `order_items.order_id`, `order_items.product_id`
  - [ ] Constraints: FKs com `cascade on delete` em itens
  - [ ] Check constraints (quando suportado) para `quantity > 0`, `stock >= 0`
  - [ ] Enum status via `string` + validation, ou `enum` nativo quando suportado
- [ ] Models Eloquent com `$fillable` explícito e casts
  - [ ] `Product` ($fillable, casts: price decimal:2, is_active bool)
  - [ ] `Order` ($fillable, casts: total decimal:2, status string, code uuid)
  - [ ] `OrderItem` ($fillable, casts: unit_price decimal:2)
- [ ] Factories e seeders mínimos
  - [ ] `ProductFactory` com `slug` único
  - [ ] Seeder de usuários: Admin e Cliente (dados do desafio)
  - [ ] Seeder de produtos exemplo

### 3) Autorização e perfis

- [ ] Adicionar coluna `role` em `users` (enum: admin, client)
  - [ ] Migration + cast + accessor helper `isAdmin()`
  - [ ] Popular via seeder: admin/cliente
- [ ] Policies
  - [ ] `OrderPolicy`: cliente só acessa os próprios pedidos
  - [ ] `ProductPolicy`/`OrderPolicy`: admin pode gerenciar produtos e pedidos
  - [ ] Registrar no `AuthServiceProvider`
  - [ ] Aplicar via `authorize()` nos controllers/rotas

### 4) Serviços de domínio e regras de negócio (app/Services)

- [ ] `ProductService`
  - [ ] CRUD de produto com validações e slug único
- [ ] `CartService` (em sessão)
  - [ ] Adicionar/remover/atualizar itens, calcular total, contar itens
  - [ ] Impedir adicionar quantidade superior ao estoque
- [ ] `OrderService`
  - [ ] Criar pedido a partir do carrinho (transação)
  - [ ] Confirmar pagamento (idempotente):
    - [ ] Recalcular total e travar linhas de produto (`for update`)
    - [ ] Debitar estoque (nunca negativo); conflito => HTTP 409
    - [ ] Disparar evento `OrderPaid`

### 5) Validações (Form Requests) e DTOs

- [ ] `ProductStoreRequest` / `ProductUpdateRequest`
  - [ ] name: required|string; price: required|numeric|min:0; stock: int|min:0; is_active: bool; slug: sometimes|unique
- [ ] `CartAddRequest` (AJAX): product_id exists, quantity int|min:1
- [ ] `OrderCheckoutRequest`: sem payload (usa sessão) mas `authorize()` cliente autenticado
- [ ] `OrderPayRequest` (admin): status target = paid

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
