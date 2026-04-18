<?php

namespace App\Domain\Encounter\Events;

use App\Domain\Encounter\Models\TeamMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamMemberRefused
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TeamMember $member,
    ) {}
}
