<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Guards: segue-me API

> DO/DON'T rules derived from codebase patterns and architecture constraints.

## Actions

| DO | DON'T |
|----|-------|
| Receive fully loaded entities as parameters | Receive IDs and re-query inside the action |
| Throw domain exceptions for business rule violations | Throw `ValidationException` from domain layer |
| Wrap state mutations in `DB::transaction()` | Mutate state across multiple calls without transaction |
| Fire domain events after mutations | Emit events inside DTO construction |
| Inject repository interfaces via constructor | Access Eloquent models directly (prefer repos) |

## DTOs

| DO | DON'T |
|----|-------|
| Use `final readonly class` | Use mutable DTOs |
| Extract `parish_id` from `$request->user()` | Call `auth()` or `Auth::id()` inside a DTO |
| Build via static `fromRequest()` factory | Build DTOs in action `execute()` bodies |

## Controllers

| DO | DON'T |
|----|-------|
| Resolve entity from repository in controller | Resolve entities inside actions |
| Call `$this->authorize()` before action dispatch | Skip authorization checks |
| Return `Resource::make()->response()->setStatusCode(201)` for create | Return raw arrays from controllers |
| Inject `AuditLogger` for significant state changes | Inject `AuditLogger` into actions |

## Models

| DO | DON'T |
|----|-------|
| Use `HasUuid` for all models | Use auto-increment integer IDs |
| Use `BelongsToParish` for tenant-scoped models | Access cross-parish data without explicit scope removal |
| Define casts via `casts(): array` method | Use `$casts` property (Laravel 13 style) |
| Use `SoftDeletes` for entities that may be archived | Hard-delete records with business history |

## Exceptions

| DO | DON'T |
|----|-------|
| Create named domain exception class in `app/Exceptions/` | Return error arrays from domain layer |
| Register exception in `bootstrap/app.php` render closure | Handle domain exceptions in controllers |
| Let domain exceptions bubble to the exception handler | Catch domain exceptions inside actions |

## Cache

| DO | DON'T |
|----|-------|
| Use `CacheKey::*` static methods for all cache keys | Write inline cache key strings |
| Forget cache in the same transaction/action that mutates data | Forget cache outside the mutation context |

## Multi-tenancy

| DO | DON'T |
|----|-------|
| Pass `parish_id` explicitly through DTOs | Read `parish_id` from `auth()` at domain layer |
| Trust `BelongsToParish` global scope for queries | Write manual `WHERE parish_id = ?` clauses |
| Use `withoutGlobalScope(ParishScope::class)` for admin queries | Disable global scopes globally |

## AI

| DO | DON'T |
|----|-------|
| Dispatch AI calls via queued Jobs | Call `ClaudeService` synchronously in controllers |
| Use prompt classes in `Domain/AI/Prompts/` | Inline prompt strings in jobs or services |
| Use `completeAsJson()` for structured AI responses | Parse JSON manually from `complete()` output |
