<!-- mustard:generated at:2026-04-01T20:20:00Z role:api -->

# Action Pattern — Examples

## Simple Action (no transaction)

```php
namespace App\Domain\People\Actions;

class UpdatePerson {
    public function __construct(private readonly PersonRepositoryInterface $people) {}

    public function execute(Person $person, UpdatePersonDTO $dto): Person {
        return $this->people->update($person, $dto->toArray());
    }
}
```
Ref: `app/Domain/People/Actions/UpdatePerson.php`

## Action with Business Rule Guards

```php
namespace App\Domain\Encounter\Actions;

class AllocatePersonToTeam {
    public function execute(Team $team, Person $person, string $role = 'member'): TeamMember {
        $encounter = $team->encounter;
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);
        throw_if($team->isFull(), TeamFullException::class);
        throw_if(!$team->accepted_type->accepts($person->type), IncompatiblePersonTypeException::class);
        $existing = $this->members->findByPersonAndEncounter($person->id, $encounter->id);
        throw_if($existing, PersonAlreadyAllocatedException::class);
        return DB::transaction(function () use ($team, $person, $role) {
            $member = $this->members->create([
                'team_id' => $team->id, 'person_id' => $person->id,
                'role' => $role, 'status' => TeamMemberStatus::Pending->value,
                'invited_at' => now(),
            ]);
            Cache::forget(CacheKey::teamSuggestions($team->id));
            event(new PersonAllocated($member));
            return $member->load('person');
        });
    }
}
```
Ref: `app/Domain/Encounter/Actions/AllocatePersonToTeam.php`

## Action dispatching queued Job (AI analysis)

```php
// Controller dispatches job — action not needed here
// App\Jobs\GenerateEncounterAnalysis dispatches ClaudeService
```
Ref: `app/Jobs/GenerateEncounterAnalysis.php`
