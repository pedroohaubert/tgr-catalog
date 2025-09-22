# Docker Development Environment

Este projeto inclui uma configuração Docker completa para desenvolvimento local.

## 🚀 Primeira execução

```bash
make up
```

Isso irá:
- Construir as imagens Docker
- Instalar dependências do Composer
- Criar/configurar o arquivo `.env`
- Executar migrations
- Executar seeders
- Iniciar servidor Laravel (porta 8000) e Vite (porta 5173)

## 📋 Comandos disponíveis

### Docker básico
```bash
make up          # Subir containers
make down        # Parar containers
make shell       # Acessar shell do container app
```

### Laravel Artisan
```bash
make migrate     # Executar migrations
make seed        # Executar seeders
make fresh       # Reset DB + seed
make test        # Executar testes
make tinker      # Abrir Tinker
```

### Artisan genérico
```bash
make artisan CMD="make:model Product"
make artisan CMD="route:list"
```

### Reset completo
```bash
make reset-db    # Para containers, remove DB/bootstrap e sobe novamente
```

## 🌐 URLs de acesso

- **Aplicação Laravel**: http://localhost:8000
- **Vite (HMR)**: http://localhost:5173

## 📁 Estrutura

- `Dockerfile` - Imagem PHP-FPM para Laravel
- `docker-compose.yml` - Serviços app e vite
- `docker/php/entrypoint.sh` - Script de inicialização
- `Makefile` - Comandos de atalho

## 🔧 Desenvolvimento

Todos os arquivos são bind-mounted, então mudanças no código são refletidas automaticamente. O Vite usa hot reload com polling para funcionar bem com containers.

## 🗃️ Banco de dados

- Usa SQLite (`database/database.sqlite`)
- Persistente entre restarts
- Migrations e seeds executam automaticamente na primeira execução
- Para reset: `make reset-db`
