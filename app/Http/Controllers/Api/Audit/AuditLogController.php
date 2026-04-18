<?php

namespace App\Http\Controllers\Api\Audit;

use App\Domain\Audit\Models\AuditLog;
use App\Http\Controllers\Controller;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Apenas admins visualizam logs
        abort_unless(
            $request->user()->hasAnyRole([
                UserRole::SuperAdmin->value,
                UserRole::DioceseAdmin->value,
                UserRole::SectorAdmin->value,
                UserRole::ParishAdmin->value,
            ]),
            403
        );

        $request->validate([
            'action' => ['nullable', 'string'],
            'model_type' => ['nullable', 'string'],
            'user_id' => ['nullable', 'uuid'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = AuditLog::with('user:id,name,email')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = '%'.$request->string('search').'%';
            $query->where(
                fn ($q) => $q->where('description', 'like', $term)
                    ->orWhere('action', 'like', $term)
            );
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%'.$request->model_type.'%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Admins de paróquia só veem logs de usuários da própria paróquia
        $authUser = $request->user();
        if ($authUser->hasRole(UserRole::ParishAdmin->value)) {
            $query->whereHas('user', fn ($q) => $q->where('parish_id', $authUser->parish_id));
        }

        $logs = $query->paginate($request->integer('per_page', 50));

        return response()->json([
            'data' => $logs->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'model_type' => class_basename($log->model_type ?? ''),
                'model_id' => $log->model_id,
                'metadata' => $log->metadata,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toISOString(),
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
            ]),
            'meta' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
