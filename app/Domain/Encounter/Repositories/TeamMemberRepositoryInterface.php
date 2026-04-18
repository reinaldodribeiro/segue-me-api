<?php

namespace App\Domain\Encounter\Repositories;

use App\Domain\Encounter\Models\TeamMember;

interface TeamMemberRepositoryInterface
{
    public function findOrFail(string $id): TeamMember;

    public function create(array $data): TeamMember;

    public function update(TeamMember $member, array $data): TeamMember;

    public function delete(TeamMember $member): void;

    public function findByPersonAndEncounter(string $personId, string $encounterId): ?TeamMember;
}
