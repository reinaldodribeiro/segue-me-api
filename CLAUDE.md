# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Framework**: Laravel ^13.0, PHP ^8.3
- **Auth**: Laravel Sanctum ^4.3
- **Authorization**: Spatie Laravel Permission ^7.2 (roles: `super_admin`, `parish_admin`, `coordinator`)
- **Queue**: Laravel Horizon ^5.45
- **Testing**: PestPHP ^4.4 + pest-plugin-laravel ^4.1 (SQLite in-memory)
- **API docs**: Dedoc Scramble ^0.13.16
- **Spreadsheet**: Maatwebsite Excel ^3.1
- **PDF**: barryvdh/laravel-dompdf ^3.1
- **AI**: Anthropic Claude via direct HTTP (`App\Domain\AI\Services\ClaudeService`)
- **Code style**: Laravel Pint ^1.27

## Commands

```bash
# Start all services (server, queue, logs, vite)
composer dev

# Run all tests
composer test

# Run specific test suite
./vendor/bin/pest --testsuite=Domain
./vendor/bin/pest --testsuite=Feature
./vendor/bin/pest --testsuite=Unit

# Run a single test file
./vendor/bin/pest tests/Domain/Encounter/Actions/AllocatePersonToTeamTest.php

# Migrations
php artisan migrate
php artisan migrate:refresh --seed

# Create a migration
php artisan make:migration create_{table}_table

# Code style
./vendor/bin/pint

# Initial project setup
composer setup

# Horizon queue dashboard
php artisan horizon

# Tail logs
php artisan pail --timeout=0
```

## Architecture

This is a **Laravel 13 REST API** using **Domain-Driven Design (DDD)** with the **Action pattern**.

### Layer Structure

```
app/
├── Domain/                  # Business logic (Encounter, Parish, People, AI, Audit)
│   └── {Domain}/
│       ├── Actions/         # Use cases — each has an execute() method
│       ├── DTOs/            # Input data for actions
│       ├── Models/          # Eloquent models
│       ├── Services/        # Domain services (e.g. EngagementScoreCalculator)
│       ├── Repositories/    # Interfaces only
│       ├── Events/
│       └── Listeners/
├── Http/
│   ├── Controllers/Api/     # Invoke actions, return Resources
│   ├── Requests/            # Form validation
│   └── Resources/           # JSON response formatting
├── Infrastructure/
│   ├── Repositories/        # Eloquent implementations of repository interfaces
│   └── Scopes/ParishScope   # Global scope for multi-tenancy
└── Support/
    ├── CacheKey             # Centralizes all cache key patterns (never use magic strings)
    ├── Traits/              # HasUuid, BelongsToParish
    └── Enums/               # PersonType, TeamMemberStatus, EncounterStatus, etc.
```

### Key Concepts

**Multi-tenancy:** Every model scoped to a `parish_id` via the `BelongsToParish` trait and `ParishScope` global scope. Actions receive `parish_id` in their DTOs to enforce isolation.

**Actions:** The primary place for business logic. Controllers are thin — they validate via Form Requests, **resolve entities from repositories**, call an Action with those entities, and return a Resource. Actions receive already-loaded entities, not IDs. Actions are bound to the container and injected via `app(SomeAction::class)->execute(...)`.

**Repositories:** Interfaces are defined in `Domain/{Domain}/Repositories/`. Eloquent implementations live in `Infrastructure/Repositories/`. Bound in `Infrastructure/Providers/RepositoryServiceProvider`.

**Exception handling:** Defined in `bootstrap/app.php`. Domain exceptions (`TeamFullException`, `PersonAlreadyAllocatedException`, `IncompatiblePersonTypeException`, `EncounterNotEditableException`, `EncounterConfirmedEditException`, `ConfirmedMemberRemovalException`) return 422 automatically. Never throw `ValidationException` from domain actions — always create a domain exception and register it in `bootstrap/app.php`.

**DTOs:** Never use `auth()` inside DTOs — pass `$request->user()->parish_id` from the controller explicitly. DTOs use `$request->user()` when built via `fromRequest()`.

**Cache keys:** Always use `App\Support\CacheKey` static methods. Never write magic strings like `'narrative:' . $id` inline.

### Domains

- **Parish** — Diocese → Sector → Parish hierarchy
- **People** — Person entities with types (Youth, Coordinator, etc.)
- **Encounter** — Movement templates → Encounters → Teams → TeamMembers. Encounters have status: `Draft`, `Confirmed`, `InProgress`, `Completed`
- **AI** — Anthropic Claude integration for auto team assembly (`AutoAssembleTeams` action) and replacement suggestions

### Auth

Laravel Sanctum (token-based). All `/api` routes require `auth:sanctum` except `POST /api/auth/login`.

Roles (via Spatie Permission): `SuperAdmin`, `ParishAdmin`, `Coordinator`.

### Testing

Tests use PestPHP with SQLite in-memory database. Test suites: `Unit`, `Feature`, `Domain`. Domain tests test Actions directly (not via HTTP). Seeder credentials: `admin@segue-me.app`, `parish@segue-me.app`, `coord@segue-me.app` — all with password `password`.

### Key Dependencies

- **Auth:** `laravel/sanctum`
- **Authorization:** `spatie/laravel-permission`
- **Queue monitoring:** `laravel/horizon`
- **API docs:** `dedoc/scramble`
- **Spreadsheet import:** `maatwebsite/excel`
- **PDF export:** `barryvdh/laravel-dompdf`
- **AI:** Anthropic Claude via HTTP (`config/services.php` → `anthropic.key`, `anthropic.model`)

## Guards

- Never use `auth()` or `Auth::id()` inside DTOs or Actions — receive entities/IDs from controller
- Never throw `ValidationException` from domain layer — create named domain exception + register in `bootstrap/app.php`
- New domain exceptions must be registered in `bootstrap/app.php` render closure returning 422
- All models must use `HasUuid` (no auto-increment IDs) and `BelongsToParish` for tenant scope
- Always use `App\Support\CacheKey` static methods — never inline cache key strings
- Controllers must call `$this->authorize()` before any action dispatch
- `AuditLogger` is injected into controller methods, not into Actions or repositories
- AI calls must be dispatched via queued Jobs — never call `ClaudeService` synchronously in controllers

## Scan References

| File | Description |
|------|-------------|
| `.claude/commands/stack.md` | Technology stack, directory structure, all tooling commands |
| `.claude/commands/modules.md` | Domain modules, controller→action map, async jobs, route groups |
| `.claude/commands/patterns.md` | 12 recurring code patterns with file references |
| `.claude/commands/guards.md` | DO/DON'T rules for all layers |
| `.claude/commands/recipes.md` | Implementation recipes: new entity, new action, AI feature |
| `.claude/commands/notes.md` | Manual notes — never overwritten |

## Recommended Skills

- `api-action-pattern` — Action class structure with execute(), entities, transactions, events
- `api-dto-pattern` — final readonly DTOs with fromRequest() and parish_id extraction
- `api-repository-pattern` — Interface/implementation split, pagination, filter patterns
- `api-controller-wiring` — Thin controller pattern: authorize → resolve → DTO → action → Resource
- `api-domain-exception` — Creating and registering domain exceptions for 422 responses
- `api-multitenancy` — BelongsToParish trait, ParishScope, UUID primary keys
- `api-ai-integration` — ClaudeService, prompt classes, queued AI jobs, cost logging
