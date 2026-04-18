---
name: api-ai-integration
description: "Pattern for Anthropic Claude AI integration in the segue-me API.
  ClaudeService wraps HTTP calls to Claude API with auto-logging, cost tracking, and JSON parsing. AI calls run in queued jobs with Prompt classes providing structured prompts.
  Use when adding AI features, creating prompts, dispatching AI jobs, or the user says 'AI feature', 'Claude integration', 'generate analysis', 'suggest with AI', 'add AI call'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# AI Integration Pattern

`ClaudeService` is the single entry point for Anthropic API calls. Prompts live in `Domain/AI/Prompts/` as classes with static `build()`. AI calls always run in queued Jobs (never synchronously in controllers). All calls auto-logged to `AiApiLog`.

## Pattern

1. Create prompt class: `app/Domain/AI/Prompts/{Name}Prompt.php` with `static build(...): string`
2. Create queued Job: `app/Jobs/{ActionName}.php` — inject `ClaudeService`
3. Use `$claude->completeAsJson()` for structured output, `$claude->complete()` for text
4. Controller dispatches job: `dispatch(new MyJob($entity))`
5. Client polls `GET /jobs/status` for completion
6. Store result in model field or dedicated analysis model

## Example

```php
// Prompt class
class TeamAnalysisPrompt {
    public static function build(Team $team): string {
        return "Analyze team '{$team->name}' with " . $team->members->count() . " members. Return JSON: {...}";
    }
}

// Job
class GenerateEncounterAnalysis implements ShouldQueue {
    public function handle(ClaudeService $claude): void {
        $result = $claude->completeAsJson(
            prompt: EncounterAnalysisPrompt::build($this->encounter),
            action: 'encounter.analysis',
            metadata: ['encounter_id' => $this->encounter->id],
        );
        EncounterAnalysis::updateOrCreate(['encounter_id' => $this->encounter->id], $result);
    }
}
```
Ref: `app/Domain/AI/Services/ClaudeService.php`
Ref: `app/Jobs/GenerateEncounterAnalysis.php`
Ref: `app/Domain/AI/Prompts/EncounterAnalysisPrompt.php`

## References

For full examples with variants:
→ Read `references/examples.md`
