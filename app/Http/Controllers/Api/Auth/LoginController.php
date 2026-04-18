<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Encounter\Repositories\MovementRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private readonly MovementRepositoryInterface $movements,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::with(['roles', 'movements'])->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais informadas estão incorretas.'],
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'email' => ['Sua conta está inativa. Entre em contato com o administrador.'],
            ]);
        }

        $expiration = $request->boolean('remember_me')
            ? now()->addDays(30)
            : now()->addDay();

        $token = $user->createToken('api', ['*'], $expiration);

        if ($user->hasRole(UserRole::ParishAdmin->value)) {
            $movementIds = $this->movements->activeIds();
        } else {
            $movementIds = $user->movements->pluck('id');
        }

        return response()->json([
            'token' => $token->plainTextToken,
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
