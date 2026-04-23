---
name: Tutorial System Bug Fix
description: Tutorial never-complete bug root cause, patterns, and fix applied 2026-04-22
type: project
---

The tutorial system (`TutorialContext.tsx`) uses `data-tutorial="step-id"` attributes on DOM elements. When steps reference missing DOM elements, it skips forward. The bug: when all remaining steps are skipped (exhausted), `setIsActive(false)` was called WITHOUT `markRouteAsSeen()`, so the tutorial restarted on every visit.

**Fix applied (2026-04-22):**
- In the skip-loop `useEffect`: when `idx >= filteredSteps.length` AND `currentStep > 0`, call `markRouteAsSeen(activeRoute)` before `setIsActive(false)`.
- Added comment to `nextStep()` clarifying that if next step has no DOM element, the useEffect continues to skip and will call markRouteAsSeen because `currentStep > 0`.

**data-tutorial attribute coverage added:**
- `People/New`: useTutorial + new-person-type, new-person-photo, new-person-basic-fields, new-person-skills, new-person-experiences
- `People/Detail`: useTutorial + person-detail-header, person-detail-engagement-score, person-detail-edit-form, person-detail-history (wrapper div), person-detail-experiences (wrapper div)
- `Users/New`: useTutorial + new-user-basic, new-user-role, new-user-hierarchy
- `Users/Detail`: useTutorial + user-detail-form (SectionCard), user-detail-toggle-active (button wrapper)
- `Movements/Detail`: useTutorial + movement-detail-info, movement-detail-teams, movement-detail-add-team
- `Encounters/Teams/index.tsx`: useTutorial added
- `Encounters/Teams/StatsBar`: teams-stats-bar on outer div
- `Encounters/Teams/PeoplePanel`: teams-people-panel on sticky div
- `Encounters/Teams/TeamMapGrid`: teams-grid on grid div
- `Encounters/Teams/AddMemberModal`: teams-ai-suggest on the "Sugestões IA" tab button (via tutorialId field in mapped array)
- `Encounters/Detail`: encounter-detail-status on status transitions div
- `Encounters/New`: new-encounter-name (wrapper div), new-encounter-location (wrapper div)
- `Movements/New`: new-movement-audience (wrapper div around Select)
- `Parishes/List`: useTutorial added

**Why:** Tutorial was silently failing to mark routes as seen when late steps had no DOM elements, causing infinite re-trigger on every page visit.

**How to apply:** When adding new tutorial steps, always add both `data-tutorial` on a stable DOM element AND call `useTutorial()` in the feature component.
