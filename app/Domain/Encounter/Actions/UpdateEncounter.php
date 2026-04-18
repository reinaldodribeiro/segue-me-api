<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\DTOs\UpdateEncounterDTO;
use App\Domain\Encounter\Events\EncounterCompleted;
use App\Domain\Encounter\Models\Encounter;
use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Exceptions\EncounterConfirmedEditException;
use App\Exceptions\EncounterHasNoTeamsException;
use App\Exceptions\EncounterNotEditableException;
use App\Support\Enums\EncounterStatus;
use Illuminate\Support\Facades\DB;

class UpdateEncounter
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function execute(Encounter $encounter, UpdateEncounterDTO $dto): Encounter
    {
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        $advancingStatus = $dto->status !== null
            && in_array($dto->status, [EncounterStatus::Confirmed, EncounterStatus::Completed]);

        if ($advancingStatus) {
            throw_if($encounter->teams()->count() === 0, EncounterHasNoTeamsException::class);

            $incompleteTeams = $encounter->teams()
                ->withCount(['members as active_count' => fn ($q) => $q->whereNotIn('status', ['refused'])])
                ->get()
                ->filter(fn ($team) => $team->active_count < $team->min_members)
                ->pluck('name');

            if ($incompleteTeams->isNotEmpty()) {
                throw new EncounterHasNoTeamsException(
                    'As seguintes equipes estão abaixo do mínimo de integrantes: '.$incompleteTeams->join(', ').'.'
                );
            }
        }

        $data = array_filter([
            'name' => $dto->name,
            'responsible_user_id' => $dto->responsibleUserId,
            'date' => $dto->date,
            'duration_days' => $dto->durationDays,
            'location' => $dto->location,
            'status' => $dto->status?->value,
            'max_participants' => $dto->maxParticipants,
        ], fn ($value) => ! is_null($value));

        if ($encounter->isConfirmed()) {
            $nonStatusKeys = array_diff(array_keys($data), ['status']);
            throw_if(! empty($nonStatusKeys), EncounterConfirmedEditException::class);
        }

        $updated = DB::transaction(function () use ($encounter, $data) {
            return $this->encounters->update($encounter, $data);
        });

        $updated->load(['movement', 'responsibleUser', 'teams.members.person']);

        if ($dto->status === EncounterStatus::Completed) {
            event(new EncounterCompleted($updated));
        }

        return $updated;
    }
}
