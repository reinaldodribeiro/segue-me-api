<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Stack: segue-me API

> Technology stack, structure, and tooling for the Laravel 13 DDD REST API.

## Runtime

| Concern | Technology | Version |
|---------|-----------|---------|
| Language | PHP | ^8.3 |
| Framework | Laravel | ^13.0 |
| Auth | Laravel Sanctum | ^4.3 |
| Authorization | Spatie Laravel Permission | ^7.2 |
| Queue monitoring | Laravel Horizon | ^5.45 |
| API docs | Dedoc Scramble | ^0.13.16 |
| Spreadsheet import/export | Maatwebsite Excel | ^3.1 |
| PDF export | barryvdh/laravel-dompdf | ^3.1 |
| AI | Anthropic Claude (direct HTTP) | — |
| Localization | laravel-lang/common | ^6.8 |

## Dev Dependencies

| Tool | Version | Purpose |
|------|---------|---------|
| PestPHP | ^4.4 | Testing |
| pest-plugin-laravel | ^4.1 | Laravel test helpers |
| PHPUnit | ^12.5 | Test runner |
| Laravel Pint | ^1.27 | Code style (PSR-12) |
| Laravel Pail | ^1.2.5 | Log tailing |
| Faker | ^1.23 | Test data generation |

## Directory Structure

```
app/
├── Domain/{Domain}/         # Business logic (Encounter, Parish, People, AI, Audit)
│   ├── Actions/             # Use cases — execute() method
│   ├── DTOs/                # Input data for actions
│   ├── Models/              # Eloquent models
│   ├── Services/            # Domain services
│   ├── Repositories/        # Interfaces only
│   ├── Events/
│   └── Listeners/
├── Http/
│   ├── Controllers/Api/     # Thin controllers — validate, authorize, call action
│   ├── Requests/            # FormRequest validation classes
│   └── Resources/           # JSON response formatters
├── Infrastructure/
│   ├── Repositories/        # Eloquent implementations
│   └── Scopes/              # ParishScope (global multi-tenancy)
├── Support/
│   ├── CacheKey             # Centralized cache key patterns
│   ├── Traits/              # HasUuid, BelongsToParish
│   └── Enums/               # PersonType, TeamMemberStatus, EncounterStatus, etc.
├── Jobs/                    # Async jobs: spreadsheet import, OCR, AI analysis
├── Imports/                 # Maatwebsite import classes
└── Exports/                 # Maatwebsite export classes
```
Ref: `app/Domain/Encounter/`, `app/Infrastructure/`, `app/Support/`

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

# Create a new migration
php artisan make:migration create_{table}_table

# Lint / code style
./vendor/bin/pint

# Initial project setup
composer setup

# Queue worker (manual)
php artisan queue:listen --tries=1 --timeout=0

# Horizon dashboard
php artisan horizon

# Tail logs
php artisan pail
```
