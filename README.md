# Segue-me — API

API REST para gerenciamento de encontros paroquiais. Permite que paróquias organizem **movimentos**, **encontros** e **equipes**, com alocação de pessoas assistida por inteligência artificial.

---

## Sumário

- [Visão Geral](#visão-geral)
- [Stack](#stack)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Rodando o Projeto](#rodando-o-projeto)
- [Usuários de Desenvolvimento](#usuários-de-desenvolvimento)
- [Endpoints da API](#endpoints-da-api)
- [Funcionalidades de IA](#funcionalidades-de-ia)
- [Arquitetura](#arquitetura)
- [Testes](#testes)

---

## Visão Geral

O Segue-me é uma plataforma multi-tenant voltada para coordenadores de movimentos paroquiais (Encontros de Casais, Jovens, etc.). Cada paróquia opera de forma isolada com seus próprios dados.

**Fluxo principal:**
1. A paróquia cadastra um **Movimento** (ex: Encontro de Casais) com templates de equipes
2. Cria um **Encontro** — uma edição concreta do movimento, com data e local
3. Gerencia **Pessoas** participantes (jovens, casais, coordenadores)
4. Aloca pessoas às **Equipes** do encontro manualmente ou via IA
5. Acompanha confirmações/recusas e gera relatórios

---

## Stack

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 13 (PHP 8.3+) |
| Banco de dados | PostgreSQL 15 |
| Cache / Filas | Redis |
| Autenticação | Laravel Sanctum (token) |
| Autorização | Spatie Permission (roles) |
| Monitoramento de filas | Laravel Horizon |
| IA | Anthropic Claude API |
| Documentação da API | Scramble (OpenAPI automático) |
| Exportação Excel | Maatwebsite Excel |
| Geração de PDF | DomPDF |
| Testes | PestPHP v4 |

---

## Pré-requisitos

- PHP 8.3+
- Composer
- Node.js 20+ e npm
- PostgreSQL 15
- Redis

---

## Instalação

```bash
# 1. Clone o repositório
git clone <url-do-repo> segue-me-api
cd segue-me-api

# 2. Configure o ambiente
cp .env.example .env

# 3. Setup completo (instala deps, gera chave, migra, compila assets)
composer setup
```

O `composer setup` executa:
- `composer install`
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install && npm run build`

### Configuração do banco

No `.env`, ajuste para PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=segue_me
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

### Populando com dados de exemplo

```bash
php artisan db:seed
```

---

## Variáveis de Ambiente

| Variável | Descrição | Padrão |
|---|---|---|
| `APP_NAME` | Nome da aplicação | `Segue-me` |
| `APP_ENV` | Ambiente (`local`, `production`) | `local` |
| `APP_URL` | URL base da API | `http://localhost:8000` |
| `APP_LOCALE` | Idioma padrão | `pt_BR` |
| `DB_CONNECTION` | Driver do banco | `pgsql` |
| `DB_HOST` | Host do PostgreSQL | `127.0.0.1` |
| `DB_PORT` | Porta do PostgreSQL | `5432` |
| `DB_DATABASE` | Nome do banco | `segue_me` |
| `DB_USERNAME` | Usuário do banco | `postgres` |
| `DB_PASSWORD` | Senha do banco | — |
| `REDIS_HOST` | Host do Redis | `127.0.0.1` |
| `REDIS_PORT` | Porta do Redis | `6379` |
| `SESSION_DRIVER` | Driver de sessão | `redis` |
| `CACHE_STORE` | Driver de cache | `redis` |
| `QUEUE_CONNECTION` | Driver de filas | `redis` |
| `ANTHROPIC_API_KEY` | Chave da API da Anthropic | — |
| `ANTHROPIC_MODEL` | Modelo Claude a usar | `claude-sonnet-4-6` |

> As funcionalidades de IA (montagem automática de equipes, OCR de fichas, narrativas) exigem `ANTHROPIC_API_KEY` configurada.

---

## Rodando o Projeto

### Modo desenvolvimento (todos os serviços juntos)

```bash
composer dev
```

Sobe em paralelo:
- `php artisan serve` → API em `http://localhost:8000`
- `php artisan queue:listen` → Worker de filas
- `php artisan pail` → Log em tempo real no terminal
- `npm run dev` → Vite (assets frontend)

### Serviços individualmente

```bash
# Apenas a API
php artisan serve

# Worker de filas (necessário para IA e importações assíncronas)
php artisan queue:work

# Monitor do Horizon (interface web das filas)
php artisan horizon
# Acesse: http://localhost:8000/horizon
```

### Documentação interativa da API (OpenAPI)

Gerada automaticamente pelo Scramble:

```
http://localhost:8000/docs/api
```

---

## Usuários de Desenvolvimento

Criados pelo seeder (`php artisan db:seed`):

| E-mail | Senha | Papel | Acesso |
|---|---|---|---|
| `admin@segue-me.app` | `password` | `super_admin` | Tudo — sem filtro de paróquia |
| `parish@segue-me.app` | `password` | `parish_admin` | Dados da própria paróquia |
| `coord@segue-me.app` | `password` | `coordinator` | Dados da própria paróquia |

### Autenticação

```bash
# Login
POST /api/auth/login
{
  "email": "parish@segue-me.app",
  "password": "password"
}

# Resposta: { "token": "...", "user": {...} }

# Usar o token em todas as requisições subsequentes:
Authorization: Bearer <token>
```

---

## Endpoints da API

Todos os endpoints (exceto login) exigem `Authorization: Bearer <token>`.
Base URL: `/api`

### Autenticação

| Método | Rota | Descrição |
|---|---|---|
| `POST` | `/auth/login` | Login — retorna token Sanctum |
| `POST` | `/auth/logout` | Logout — revoga o token atual |
| `GET` | `/auth/me` | Dados do usuário autenticado |

### Usuários

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/users` | Listar usuários |
| `POST` | `/users` | Criar usuário |
| `GET` | `/users/{id}` | Detalhar usuário |
| `PUT` | `/users/{id}` | Atualizar usuário |
| `DELETE` | `/users/{id}` | Remover usuário |
| `PATCH` | `/users/{id}/toggle-active` | Ativar/desativar usuário |

### Diocese / Setor / Paróquia

| Método | Rota | Descrição |
|---|---|---|
| `GET/POST` | `/dioceses` | Listar / Criar dioceses |
| `GET/PUT/DELETE` | `/dioceses/{id}` | Detalhar / Atualizar / Remover |
| `GET/POST` | `/dioceses/{id}/sectors` | Setores aninhados na diocese |
| `GET/PUT/DELETE` | `/sectors/{id}` | Detalhar / Atualizar / Remover setor |
| `GET/POST` | `/sectors/{id}/parishes` | Paróquias aninhadas no setor |
| `GET/PUT/DELETE` | `/parishes/{id}` | Detalhar / Atualizar / Remover paróquia |
| `POST` | `/parishes/{id}/logo` | Upload do logotipo |
| `GET/POST/DELETE` | `/parishes/{id}/skills` | Habilidades disponíveis na paróquia |
| `GET` | `/parishes/{id}/report/engagement` | Relatório de engajamento da paróquia |

### Pessoas

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/people` | Listar pessoas (filtros: `search`, `type`, `skills`) |
| `POST` | `/people` | Cadastrar pessoa (verifica duplicatas automaticamente) |
| `GET` | `/people/{id}` | Detalhar pessoa |
| `PUT` | `/people/{id}` | Atualizar pessoa |
| `DELETE` | `/people/{id}` | Remover pessoa |
| `GET` | `/people/{id}/history` | Histórico de participações em encontros |
| `GET` | `/people/{id}/suggested-teams` | Sugestão de equipes para esta pessoa |
| `POST` | `/people/import/spreadsheet` | Importação em massa via Excel/CSV |
| `POST` | `/people/import/scan` | Importação via scan de ficha (OCR com IA) |
| `GET` | `/people/import/status?cache_key=...` | Status de um job de importação |
| `GET` | `/people/import/template` | Download do modelo de planilha |
| `GET` | `/people/export/excel` | Exportar pessoas para Excel |

#### Tipos de pessoa (`type`)

| Valor | Descrição |
|---|---|
| `youth` | Jovem |
| `couple` | Casal |
| `coordinator` | Coordenador |
| `staff` | Equipe de serviço |

### Movimentos

| Método | Rota | Descrição |
|---|---|---|
| `GET/POST` | `/movements` | Listar / Criar movimentos |
| `GET/PUT/DELETE` | `/movements/{id}` | CRUD de movimento |
| `GET/POST` | `/movements/{id}/teams` | Templates de equipe do movimento |
| `PUT/DELETE` | `/movements/{id}/teams/{teamId}` | Atualizar / Remover template |
| `POST` | `/movements/{id}/teams/reorder` | Reordenar templates de equipe |

### Encontros

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/encounters` | Listar encontros (filtros: `status`, `movement_id`) |
| `POST` | `/encounters` | Criar encontro (copia templates do movimento automaticamente) |
| `GET` | `/encounters/{id}` | Detalhar com equipes e membros |
| `PUT` | `/encounters/{id}` | Atualizar encontro |
| `DELETE` | `/encounters/{id}` | Remover encontro (somente `draft`) |
| `GET` | `/encounters/{id}/summary` | Resumo de ocupação das equipes |
| `GET` | `/encounters/{id}/available-people` | Pessoas disponíveis para alocar |
| `POST` | `/encounters/{id}/auto-assemble` | Montar equipes automaticamente com IA |

#### Status do encontro

| Status | Edição permitida |
|---|---|
| `draft` | Totalmente editável |
| `confirmed` | Somente o campo `status` pode mudar |
| `completed` | Imutável |

### Relatórios de Encontro

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/encounters/{id}/report/pdf` | Relatório completo em PDF |
| `GET` | `/encounters/{id}/report/narrative` | Narrativa gerada por IA (assíncrono) |
| `GET` | `/encounters/{id}/report/refusals` | Relatório de recusas |

### Equipes

| Método | Rota | Descrição |
|---|---|---|
| `GET/POST` | `/encounters/{id}/teams` | Listar / Criar equipes no encontro |
| `GET/PUT/DELETE` | `/teams/{id}` | CRUD de equipe |

### Membros de Equipe

| Método | Rota | Descrição |
|---|---|---|
| `POST` | `/teams/{id}/members` | Alocar pessoa à equipe |
| `DELETE` | `/team-members/{id}` | Remover membro (`reason` obrigatório se `confirmed`) |
| `PATCH` | `/team-members/{id}/status` | Atualizar status do membro |
| `GET` | `/team-members/{id}/suggest-replacement` | Sugerir substituto com IA |

#### Status do membro (`status`)

| Valor | Descrição |
|---|---|
| `pending` | Convidado, aguardando resposta |
| `confirmed` | Confirmou presença |
| `refused` | Recusou (campo `refusal_reason` obrigatório) |

### Jobs Assíncronos

Operações que retornam `202 Accepted` executam em background e retornam um `cache_key`. Use este endpoint para verificar o resultado:

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/jobs/status?cache_key=...` | Consultar resultado de job assíncrono |

**Resposta enquanto processa:**
```json
{ "status": "processing" }
```

**Resposta ao concluir:**
```json
{ "status": "done", "allocations": [...] }
```

**Resposta em caso de falha:**
```json
{ "status": "failed", "message": "..." }
```

### Auditoria

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/audit-logs` | Listar logs de auditoria (ações sensíveis: remoção de confirmados, conclusão de encontros, exclusão de pessoas) |

---

## Funcionalidades de IA

Todas as funções de IA usam o **Anthropic Claude** e são processadas de forma assíncrona via fila Redis.

### Montagem automática de equipes

```
POST /api/encounters/{id}/auto-assemble
```

O sistema analisa as pessoas disponíveis levando em conta o score de engajamento, histórico de participações e as habilidades recomendadas para cada equipe. Sugere uma alocação completa e retorna um `cache_key` para polling via `/jobs/status`.

### Sugestão de substituto

```
GET /api/team-members/{id}/suggest-replacement
```

Quando um membro recusa ou é removido, retorna as melhores pessoas disponíveis para substituição com base no perfil da equipe (tipo aceito, habilidades recomendadas, score de engajamento).

### OCR de fichas de inscrição

```
POST /api/people/import/scan
Content-Type: multipart/form-data
file: <PDF ou imagem JPG/PNG/WEBP>
```

Extrai dados de uma ficha física digitalizada usando visão computacional (Claude multimodal) e cadastra a pessoa automaticamente. Retorna `cache_key` para polling.

### Narrativa do encontro

```
GET /api/encounters/{id}/report/narrative
```

Gera um texto narrativo em português, entre 3 e 5 parágrafos, adequado para relatórios oficiais da paróquia, com base nos dados e estatísticas do encontro.

---

### Score de Engajamento

Cada pessoa possui um `engagement_score` calculado automaticamente a cada evento relevante:

```
score = (confirmações × 10) + (equipes distintas × 5) - (recusas × 3) + min(anos_ativo × 2, 20)
```

O score é atualizado via eventos de domínio (`PersonAllocated`, `TeamMemberConfirmed`, `TeamMemberRefused`, `EncounterCompleted`) e é o critério principal de prioridade na montagem automática de equipes.

---

## Arquitetura

O projeto usa **Domain-Driven Design (DDD)** com **Action Pattern** e **Repository Pattern**.

```
app/
├── Domain/           # Lógica de negócio pura
│   ├── AI/           # Integração com Claude (ClaudeService, Prompts)
│   ├── Audit/        # Registro de auditoria (AuditLogger, AuditLog)
│   ├── Encounter/    # Movimentos, Encontros, Equipes, Membros
│   ├── Parish/       # Diocese, Setor, Paróquia
│   └── People/       # Pessoa, EngagementScoreCalculator
│
├── Http/             # Camada de aplicação HTTP
│   ├── Controllers/  # Thin controllers — valida, resolve entidades, chama action
│   ├── Requests/     # Validação de entrada (Form Requests)
│   └── Resources/    # Formatação de resposta (API Resources)
│
├── Infrastructure/   # Implementações técnicas
│   ├── Repositories/ # Implementações Eloquent das interfaces de repositório
│   └── Scopes/       # ParishScope — multi-tenancy automático por global scope
│
├── Jobs/             # Jobs assíncronos (IA, importações)
│
└── Support/
    ├── CacheKey      # Chaves de cache centralizadas (nunca usar magic strings inline)
    ├── Enums/        # Enums com comportamento (PersonType, EncounterStatus…)
    └── Traits/       # HasUuid, BelongsToParish
```

### Multi-tenancy

Todo model com o trait `BelongsToParish` é filtrado automaticamente pelo `ParishScope` (global scope Eloquent). Usuários com role `super_admin` veem todos os dados sem filtro; demais roles enxergam apenas dados de sua paróquia.

### Regras de negócio centrais

- Um encontro com status `completed` não pode ser editado
- Um encontro com status `confirmed` só permite alterar o campo `status`
- Uma pessoa só pode estar em uma equipe ativa por encontro
- A remoção de um membro `confirmed` exige o campo `reason`
- Exceções de domínio (`TeamFullException`, `EncounterNotEditableException`, etc.) são mapeadas para HTTP 422 em `bootstrap/app.php`

---

## Testes

Os testes usam **PestPHP** com banco SQLite em memória — sem necessidade de PostgreSQL ou Redis rodando.

```bash
# Todos os testes
composer test

# Por suite
./vendor/bin/pest --testsuite=Domain
./vendor/bin/pest --testsuite=Feature
./vendor/bin/pest --testsuite=Unit

# Arquivo específico
./vendor/bin/pest tests/Domain/Encounter/Actions/AllocatePersonToTeamTest.php

# Com cobertura de código
./vendor/bin/pest --coverage
```

### Estrutura de testes

```
tests/
├── Domain/           # Testes de Actions chamadas diretamente (sem HTTP)
│   ├── Encounter/
│   ├── Parish/
│   └── People/
├── Feature/          # Testes de endpoints HTTP completos
└── Unit/             # Testes unitários isolados
```

Os testes de domínio chamam as Actions diretamente via `app(AllocatePersonToTeam::class)->execute(...)`, usando factories para criar os dados necessários. Não usam mocks de repositório — testam com banco real (SQLite in-memory).
