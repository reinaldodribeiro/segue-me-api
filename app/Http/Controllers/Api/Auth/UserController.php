<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Audit\AuditLogger;
use App\Domain\Encounter\Models\Movement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $authUser = $request->user();

        $query = User::with(['roles', 'parish'])->orderBy('name');

        // Filtra pelo escopo do usuário autenticado
        if ($authUser->hasRole([UserRole::ParishAdmin->value, UserRole::Coordinator->value])) {
            $query->where('parish_id', $authUser->parish_id);
        } elseif ($authUser->hasRole(UserRole::SectorAdmin->value)) {
            $query->where('sector_id', $authUser->sector_id);
        } elseif ($authUser->hasRole(UserRole::DioceseAdmin->value)) {
            $query->where('diocese_id', $authUser->diocese_id);
        }

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->string('role')));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return UserResource::collection(
            $query->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        $data['password'] = Hash::make($data['password']);
        $data['active'] = $data['active'] ?? true;

        $authUser = $request->user();

        // Herda o escopo do criador se não informado
        if (empty($data['parish_id']) && $authUser->parish_id) {
            $data['parish_id'] = $authUser->parish_id;
        }
        if (empty($data['sector_id']) && $authUser->sector_id) {
            $data['sector_id'] = $authUser->sector_id;
        }
        if (empty($data['diocese_id']) && $authUser->diocese_id) {
            $data['diocese_id'] = $authUser->diocese_id;
        }

        $user = User::create($data);
        $user->assignRole($role);

        return UserResource::make($user->load('roles'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): UserResource
    {
        $user = User::with(['roles'])->findOrFail($id);
        $this->authorize('view', $user);
        Log::info('User', $user->toArray());

        return UserResource::make($user);
    }

    public function update(UpdateUserRequest $request, string $id, AuditLogger $audit): UserResource
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if (isset($data['role'])) {
            $oldRole = $user->roleName();
            $newRole = $data['role'];
            $user->syncRoles([$newRole]);
            unset($data['role']);

            $audit->log(
                'user.role_changed',
                "Role do usuário \"{$user->name}\" alterada de \"{$oldRole}\" para \"{$newRole}\".",
                $user,
                ['old_role' => $oldRole, 'new_role' => $newRole]
            );
        }

        $user->update($data);

        return UserResource::make($user->refresh()->load('roles'));
    }

    public function destroy(string $id, Request $request, AuditLogger $audit): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        // Impede auto-exclusão
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Não é possível remover seu próprio usuário.'], 422);
        }

        $audit->log(
            'user.deleted',
            "Usuário \"{$user->name}\" ({$user->email}) removido.",
            $user,
            ['email' => $user->email, 'role' => $user->roleName()]
        );

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Usuário removido com sucesso.']);
    }

    public function listMovements(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);

        $movements = Movement::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $movements]);
    }

    public function syncMovements(string $id, Request $request): UserResource
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $request->validate([
            'movement_ids' => ['present', 'array'],
            'movement_ids.*' => ['uuid'],
        ]);

        $requestedIds = $request->input('movement_ids', []);
        $validIds = empty($requestedIds) ? [] :
            Movement::whereIn('id', $requestedIds)
                ->pluck('id')
                ->toArray();

        $user->movements()->sync($validIds);

        return UserResource::make($user->load(['roles', 'movements']));
    }

    public function toggleActive(string $id, Request $request, AuditLogger $audit): UserResource
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        abort_if($user->id === $request->user()->id, 422, 'Não é possível alterar o status do seu próprio usuário.');

        $user->update(['active' => ! $user->active]);

        $status = $user->active ? 'ativado' : 'desativado';
        $audit->log(
            'user.toggled_active',
            "Usuário \"{$user->name}\" {$status}.",
            $user,
            ['active' => $user->active]
        );

        if (! $user->active) {
            $user->tokens()->delete();
        }

        return UserResource::make($user->refresh()->load('roles'));
    }
}
