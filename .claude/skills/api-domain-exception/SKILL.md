---
name: api-domain-exception
description: "Pattern for creating and registering domain exceptions in the segue-me API.
  Domain exceptions extend RuntimeException, bubble up from actions, and are mapped to 422 HTTP responses in bootstrap/app.php.
  Use when adding a new business rule violation, creating a new exception type, registering an exception handler, or the user says 'add exception', 'new business rule', 'throw error', '422 response', 'business constraint'."
---
<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Domain Exception Pattern

Named exception classes in `app/Exceptions/`. Registered in `bootstrap/app.php` render closures that return 422 JSON for `api/*` routes. Actions throw with `throw_if()` or `throw new`. Never throw `ValidationException` from domain.

## Pattern

1. Create `app/Exceptions/{ExceptionName}Exception.php` extending `\RuntimeException`
2. Add message in constructor or as default
3. Register in `bootstrap/app.php` inside `->withExceptions(function (Exceptions $exceptions)`:

```php
$exceptions->render(function (MyException $e, Request $request) {
    if ($request->is('api/*')) return response()->json(['message' => $e->getMessage()], 422);
});
```

## Example

```php
// app/Exceptions/TeamFullException.php
class TeamFullException extends \RuntimeException {
    public function __construct() { parent::__construct('Esta equipe está completa.'); }
}

// In Action:
throw_if($team->isFull(), TeamFullException::class);

// bootstrap/app.php:
$exceptions->render(function (TeamFullException $e, Request $request) {
    if ($request->is('api/*')) return response()->json(['message' => $e->getMessage()], 422);
});
```
Ref: `app/Exceptions/`, `bootstrap/app.php`

## Registered exceptions

`TeamFullException` | `PersonAlreadyAllocatedException` | `IncompatiblePersonTypeException` | `EncounterNotEditableException` | `EncounterConfirmedEditException` | `ConfirmedMemberRemovalException` | `DuplicateDioceseSlugException`

## References

For full examples with variants:
→ Read `references/examples.md`
