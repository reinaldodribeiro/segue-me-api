<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Recipes: segue-me API

> Implementation index for new features, entities, and endpoints.

## Recipe: New Domain Entity (Full CRUD)

### Steps
1. Create Eloquent model in `app/Domain/{Domain}/Models/` with `HasUuid`, `BelongsToParish`, `SoftDeletes` → `patterns.md` §8
2. Create migration: `php artisan make:migration create_{table}_table`
3. Create repository interface in `app/Domain/{Domain}/Repositories/` → `patterns.md` §4
4. Create Eloquent implementation in `app/Infrastructure/Repositories/` → `patterns.md` §4
5. Bind interface in `app/Infrastructure/Providers/RepositoryServiceProvider.php`
6. Create DTO(s) in `app/Domain/{Domain}/DTOs/` → `patterns.md` §2
7. Create Action(s) in `app/Domain/{Domain}/Actions/` → `patterns.md` §1
8. Create FormRequest(s) in `app/Http/Requests/{Domain}/` → `patterns.md` §9
9. Create Resource in `app/Http/Resources/{Domain}/` → `patterns.md` §10
10. Create Controller in `app/Http/Controllers/Api/{Domain}/` → `patterns.md` §3
11. Register routes in `routes/api.php`
12. If business rules: create domain exception + register in `bootstrap/app.php` → `patterns.md` §5
13. Run tests: `./vendor/bin/pest --testsuite=Domain`

### Reference module: Encounter | Reference files:
- `app/Domain/Encounter/Models/Encounter.php`
- `app/Domain/Encounter/DTOs/CreateEncounterDTO.php`
- `app/Domain/Encounter/Actions/AllocatePersonToTeam.php`
- `app/Http/Controllers/Api/Encounter/EncounterController.php`
- `app/Infrastructure/Repositories/EloquentEncounterRepository.php`

### Task splits
- **Model + Migration** (steps 1-2): Patterns: `patterns.md` §6,§8 | Depends on: none
- **Repository** (steps 3-5): Patterns: `patterns.md` §4 | Depends on: Model + Migration
- **Action + DTO** (steps 6-7): Patterns: `patterns.md` §1,§2 | Depends on: Repository
- **HTTP Layer** (steps 8-11): Patterns: `patterns.md` §3,§9,§10 | Depends on: Action + DTO
- **Exception wiring** (step 12): Patterns: `patterns.md` §5 | Depends on: Action + DTO

### File hierarchy
| Level | Component | Depends on |
|-------|-----------|------------|
| 1 | Model + Migration | — |
| 2 | Repository Interface | Model |
| 3 | Repository Implementation | Interface |
| 4 | DTO | Model |
| 5 | Action | DTO + Repository |
| 6 | FormRequest | — |
| 7 | Resource | Model |
| 8 | Controller | Action + Resource + FormRequest |
| 9 | Route registration | Controller |
| 10 | Tests | All |

---

## Recipe: Add Action to Existing Domain

### Steps
1. Create DTO if new input shape needed → `patterns.md` §2
2. Create Action in `app/Domain/{Domain}/Actions/` → `patterns.md` §1
3. If new exception: add to `app/Exceptions/` + register in `bootstrap/app.php` → `patterns.md` §5
4. Create/update FormRequest if new validation needed → `patterns.md` §9
5. Add method to existing Controller → `patterns.md` §3
6. Add route in `routes/api.php`
7. Run tests: `./vendor/bin/pest --testsuite=Domain`

### Reference module: AllocatePersonToTeam
### Reference files: `app/Domain/Encounter/Actions/AllocatePersonToTeam.php`, `app/Http/Controllers/Api/Encounter/TeamMemberController.php`

---

## Recipe: Add AI-Powered Feature

### Steps
1. Create Prompt class in `app/Domain/AI/Prompts/` with static `build()` method
2. Create queued Job in `app/Jobs/` that calls `ClaudeService::completeAsJson()` or `complete()`
3. Update model/resource with new AI result fields
4. Add controller method to dispatch job and poll via `JobStatusController`
5. Register job status route in `routes/api.php`
6. Run tests: `./vendor/bin/pest --testsuite=Feature`

### Reference files: `app/Domain/AI/Services/ClaudeService.php`, `app/Jobs/GenerateEncounterAnalysis.php`, `app/Domain/AI/Prompts/EncounterAnalysisPrompt.php`
