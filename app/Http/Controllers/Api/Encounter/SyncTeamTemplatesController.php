<?php

namespace App\Http\Controllers\Api\Encounter;

use App\Domain\Encounter\Repositories\EncounterRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Encounter\TeamResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SyncTeamTemplatesController extends Controller
{
    public function __construct(
        private readonly EncounterRepositoryInterface $encounters,
    ) {}

    public function __invoke(string $encounterId): AnonymousResourceCollection
    {
        $encounter = $this->encounters->findOrFail($encounterId);
        $this->authorize('update', $encounter);

        $encounter->loadMissing('movement.movementTeams');

        $templates = $encounter->movement->movementTeams->keyBy('id');
        $templateIds = $templates->keys()->all();

        // Remove equipes do encontro cujo template não existe mais no movimento
        $encounter->teams()
            ->whereNotNull('movement_team_id')
            ->whereNotIn('movement_team_id', $templateIds)
            ->get()
            ->each(fn ($team) => $team->delete());

        // Mapeia equipes já existentes pelo movement_team_id
        $existingTeams = $encounter->teams()
            ->whereNotNull('movement_team_id')
            ->get()
            ->keyBy('movement_team_id');

        foreach ($templates as $template) {
            $data = [
                'name' => $template->name,
                'icon' => $template->icon,
                'min_members' => $template->min_members,
                'max_members' => $template->max_members,
                'coordinators_youth' => $template->coordinators_youth,
                'coordinators_couples' => $template->coordinators_couples,
                'accepted_type' => $template->accepted_type->value,
                'recommended_skills' => $template->recommended_skills,
                'order' => $template->order,
            ];

            if ($existingTeams->has($template->id)) {
                // Atualiza configuração, preserva membros
                $existingTeams->get($template->id)->update($data);
            } else {
                // Cria nova equipe
                $encounter->teams()->create(array_merge($data, [
                    'movement_team_id' => $template->id,
                ]));
            }
        }

        $teams = $encounter->teams()->with('members.person')->orderBy('order')->get();

        return TeamResource::collection($teams);
    }
}
