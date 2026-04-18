<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Events\TeamMemberConfirmed;
use App\Domain\Encounter\Events\TeamMemberRefused;
use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Encounter\Repositories\TeamMemberRepositoryInterface;
use App\Exceptions\EncounterNotEditableException;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Support\Facades\DB;

class UpdateMemberStatus
{
    public function __construct(
        private readonly TeamMemberRepositoryInterface $members,
    ) {}

    public function execute(TeamMember $member, TeamMemberStatus $status, ?string $refusalReason = null): TeamMember
    {
        $encounter = $member->team->encounter;
        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        return DB::transaction(function () use ($member, $status, $refusalReason) {
            $updated = $this->members->update($member, [
                'status' => $status->value,
                'refusal_reason' => $refusalReason,
                'responded_at' => now(),
            ]);

            match ($status) {
                TeamMemberStatus::Confirmed => event(new TeamMemberConfirmed($updated)),
                TeamMemberStatus::Refused => event(new TeamMemberRefused($updated)),
                default => null,
            };

            return $updated->load('person');
        });
    }
}
