<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# AI Integration — Examples

## ClaudeService complete() signature

```php
$claude->complete(
    prompt: string,
    images: array,          // [{type: 'image/jpeg', data: base64}]
    model: ?string,         // null = config default
    action: string,         // logged to AiApiLog.action
    metadata: array,        // logged to AiApiLog.metadata
    timeout: int,           // seconds, default 60
    maxTokens: int,         // default 8192
): string
```
Ref: `app/Domain/AI/Services/ClaudeService.php`

## completeAsJson() — strips markdown fences, decodes JSON

```php
$result = $claude->completeAsJson(
    prompt: MemberSuggestionPrompt::build($team, $availablePeople),
    action: 'team.suggest_members',
    metadata: ['team_id' => $team->id],
);
// Returns: array from decoded JSON
```
Ref: `app/Domain/AI/Services/ClaudeService.php`

## Vision call (OCR of ficha)

```php
$claude->complete(
    prompt: ExtractFichaPrompt::build(),
    images: [['type' => 'image/jpeg', 'data' => base64_encode($imageData)]],
    action: 'people.ocr_import',
    timeout: 90,
);
```
Ref: `app/Jobs/ProcessFichaOcr.php`, `app/Domain/AI/Prompts/ExtractFichaPrompt.php`
