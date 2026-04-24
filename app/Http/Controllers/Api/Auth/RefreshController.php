<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefreshController extends Controller
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'movements', 'parish']);

        $expiration = now()->addHours(8);

        $token = $user->createToken('api', ['*'], $expiration);

        $request->user()->currentAccessToken()->delete();

        if ($user->hasRole(UserRole::ParishAdmin->value)) {
            $movementIds = $this->movements->activeIds();
        } else {
            $movementIds = $user->movements->pluck('id');
        }

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $expiration->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
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
