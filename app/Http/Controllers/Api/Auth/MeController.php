<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeController extends Controller
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load(['parish', 'sector', 'diocese', 'roles']);

        // Fetch movement_ids with a fresh query (avoids Sanctum cached model issues)
        if ($user->hasRole(UserRole::ParishAdmin->value)) {
            $movementIds = $this->movements->activeIds();
        } else {
            $movementIds = DB::table('movement_user')
                ->where('user_id', $user->id)
                ->pluck('movement_id');
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roleName(),
                'roles' => $user->roles->pluck('name'),
                'parish_id' => $user->parish_id,
                'sector_id' => $user->sector_id,
                'diocese_id' => $user->diocese_id,
                'movement_ids' => $movementIds,
                'parish' => $user->parish ? [
                    'id' => $user->parish->id,
                    'name' => $user->parish->name,
                    'logo' => $user->parish->logo,
                    'primary_color' => $user->parish->primary_color,
                    'secondary_color' => $user->parish->secondary_color,
                ] : null,
            ],
        ]);
    }
}
