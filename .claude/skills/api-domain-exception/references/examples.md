<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Domain Exception — Examples

## Exception class (minimal)

```php
namespace App\Exceptions;

class TeamFullException extends \RuntimeException {
    public function __construct() {
        parent::__construct('Esta equipe já atingiu o limite de membros.');
    }
}
```
Ref: `app/Exceptions/TeamFullException.php`

## Registration in bootstrap/app.php

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (TeamFullException $e, Request $request) {
        if ($request->is('api/*')) return response()->json(['message' => $e->getMessage()], 422);
    });
    $exceptions->render(function (EncounterNotEditableException $e, Request $request) {
        if ($request->is('api/*')) return response()->json(['message' => $e->getMessage()], 422);
    });
    // ... all domain exceptions follow this exact pattern
})
```
Ref: `bootstrap/app.php`

## Usage in Action

```php
// Single check
throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

// With condition
throw_if(!$team->accepted_type->accepts($person->type), IncompatiblePersonTypeException::class);
```
Ref: `app/Domain/Encounter/Actions/AllocatePersonToTeam.php`
