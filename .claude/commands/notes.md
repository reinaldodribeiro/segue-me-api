# Notes: API (api)

> Manual notes for the segue-me API subproject. This file is never overwritten by scan — edit freely.

## Mandatory Patterns

- All models must use `HasUuid` and `BelongsToParish` traits for UUID primary keys and parish-scoped multi-tenancy.
- Actions receive already-loaded entities (not IDs) from controllers.
- Domain exceptions must be registered in `bootstrap/app.php` to return 422 responses automatically.
- Cache keys must always use `App\Support\CacheKey` — never inline magic strings.

## Known Pitfalls

- Never use `auth()` inside DTOs — pass `$request->user()->parish_id` from the controller explicitly.
- Never throw `ValidationException` from domain actions — always create a named domain exception.
- Repository interfaces are defined in `Domain/` but implementations live in `Infrastructure/Repositories/`.

## Observations

- AI integration uses direct HTTP calls to Anthropic Claude API (no SDK) via `ClaudeService`.
- Jobs (`GenerateEncounterAnalysis`, `ProcessSpreadsheetImport`, `ProcessFichaOcr`) are dispatched and tracked via `JobStatusController` for async polling.
- `AuditLogger` is injected into controllers that need audit trails (not into actions).
