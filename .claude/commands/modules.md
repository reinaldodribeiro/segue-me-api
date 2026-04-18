<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Modules: segue-me API

> Domain modules, their responsibilities, and complexity classification.

## Domain Map

| Domain | Models | Actions | Complexity |
|--------|--------|---------|------------|
| Encounter | Encounter, Team, TeamMember, Movement, MovementTeam, TeamEvaluation, MemberEvaluation, EncounterAnalysis, EncounterParticipant, TeamAnalysis | CreateEncounter, UpdateEncounter, AllocatePersonToTeam, RemovePersonFromTeam, UpdateMemberStatus, CreateMovement, UpdateMovement, CreateMovementTeam, UpdateMovementTeam, CopyTeamTemplates, SuggestMembersForTeam, SuggestReplacement, SuggestTeamsForPerson, GenerateEvaluationTokens | Complex |
| People | Person, PersonTeamExperience | CreatePerson, UpdatePerson, DetectDuplicates | Medium |
| Parish | Parish, Diocese, Sector | — (CRUD via repositories) | Simple |
| AI | AiApiLog | — (via ClaudeService) | Complex |
| Audit | AuditLog | — (via AuditLogger) | Simple |

Ref: `app/Domain/`

## Controller → Action Flow (Encounter example)

| Controller | Actions Used |
|-----------|-------------|
| `EncounterController` | `CreateEncounter`, `UpdateEncounter` |
| `TeamMemberController` | `AllocatePersonToTeam`, `RemovePersonFromTeam`, `UpdateMemberStatus`, `SuggestReplacement` |
| `TeamController` | `SuggestMembersForTeam` |
| `SyncTeamTemplatesController` | `CopyTeamTemplates` |
| `EncounterAnalysisController` | dispatches `GenerateEncounterAnalysis` job |
| `PersonController` | `CreatePerson`, `UpdatePerson`, `DetectDuplicates` |

Ref: `app/Http/Controllers/Api/`

## Async Jobs

| Job | Trigger | Uses |
|-----|---------|------|
| `GenerateEncounterAnalysis` | POST `/encounters/{id}/analysis/generate` | `ClaudeService` |
| `ProcessSpreadsheetImport` | POST `/people/import/spreadsheet` | Maatwebsite Excel |
| `ProcessFichaOcr` | POST `/people/import/scan` | `ClaudeService` (vision) |

Ref: `app/Jobs/`

## Route Groups

| Group | Middleware | Example routes |
|-------|-----------|---------------|
| Public | none | `POST /auth/login`, `GET /people/import/template` |
| Throttled public | `throttle:10,1` | `POST /avaliacao/{token}/verify` |
| Authenticated | `auth:sanctum` | All resource routes |

Ref: `routes/api.php`
