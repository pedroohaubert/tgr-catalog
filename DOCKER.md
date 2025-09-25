# Docker Development Environment

Este projeto inclui uma configuração Docker completa para desenvolvimento local.

## 🚀 Primeira execução

Para iniciar o projeto pela primeira vez, simplesmente execute:

```bash
make up
```

**É isso!** O Docker irá automaticamente:
- Construir as imagens Docker
- Instalar dependências do Composer e Node.js
- Criar/configurar o arquivo `.env` a partir do `.env.example`
- Configurar a chave da aplicação
- Executar migrations do banco de dados
- Executar seeders com dados iniciais
- Iniciar servidor Laravel (porta 8000) e Vite (porta 5173)

A primeira execução pode levar alguns minutos devido à instalação das dependências.

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
make reset-db       # Para containers, remove DB/bootstrap e sobe novamente
make fresh-install  # Reset completo: remove vendor, node_modules, DB e rebuild tudo
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
