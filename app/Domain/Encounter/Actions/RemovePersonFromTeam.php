<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Models\TeamMember;
use App\Domain\Encounter\Repositories\TeamMemberRepositoryInterface;
use App\Exceptions\ConfirmedMemberRemovalException;
use App\Exceptions\EncounterNotEditableException;
use App\Support\CacheKey;
use App\Support\Enums\TeamMemberStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RemovePersonFromTeam
{
    public function __construct(
        private readonly TeamMemberRepositoryInterface $members,
    ) {}

    public function execute(TeamMember $member, ?string $reason = null): void
    {
        $encounter = $member->team->encounter;

        throw_if($encounter->isCompleted(), EncounterNotEditableException::class);

        if ($member->status === TeamMemberStatus::Confirmed && ! $reason) {
            throw new ConfirmedMemberRemovalException;
        }

        $teamId = $member->team_id;

        DB::transaction(fn () => $this->members->delete($member));

        Cache::forget(CacheKey::teamSuggestions($teamId));
    }
}
