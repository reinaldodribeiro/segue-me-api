<?php

namespace App\Http\Controllers\Api\AI;

use App\Domain\AI\Models\AiApiLog;
use App\Http\Controllers\Controller;
use App\Support\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiApiLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasRole(UserRole::SuperAdmin->value),
            403
        );

        $request->validate([
            'action' => ['nullable', 'string'],
            'success' => ['nullable', 'in:true,false,1,0'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = AiApiLog::with('user:id,name,email')
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('success')) {
            $query->where('success', $request->boolean('success'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => $logs->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'model' => $log->model,
                'input_tokens' => $log->input_tokens,
                'output_tokens' => $log->output_tokens,
                'total_tokens' => $log->total_tokens,
                'estimated_cost_usd' => $log->estimated_cost_usd,
                'success' => $log->success,
                'error_message' => $log->error_message,
                'duration_ms' => $log->duration_ms,
                'metadata' => $log->metadata,
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

    public function stats(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->hasRole(UserRole::SuperAdmin->value),
            403
        );

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = AiApiLog::query();

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $totals = $query->selectRaw('
            COUNT(*) as total_calls,
            SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_calls,
            SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_calls,
            SUM(total_tokens) as total_tokens,
            SUM(input_tokens) as total_input_tokens,
            SUM(output_tokens) as total_output_tokens,
            SUM(estimated_cost_usd) as total_cost_usd,
            AVG(duration_ms) as avg_duration_ms
        ')->first();

        $byAction = (clone $query)->selectRaw('
            action,
            COUNT(*) as calls,
            SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful,
            SUM(total_tokens) as tokens,
            SUM(estimated_cost_usd) as cost_usd
        ')
            ->groupBy('action')
            ->orderByDesc('calls')
            ->get()
            ->map(fn ($row) => [
                'action' => $row->action,
                'calls' => (int) $row->calls,
                'successful' => (int) $row->successful,
                'tokens' => (int) $row->tokens,
                'cost_usd' => round((float) $row->cost_usd, 6),
            ]);

        $byModel = (clone $query)->selectRaw('
            model,
            COUNT(*) as calls,
            SUM(total_tokens) as tokens,
            SUM(estimated_cost_usd) as cost_usd
        ')
            ->groupBy('model')
            ->orderByDesc('calls')
            ->get()
            ->map(fn ($row) => [
                'model' => $row->model,
                'calls' => (int) $row->calls,
                'tokens' => (int) $row->tokens,
                'cost_usd' => round((float) $row->cost_usd, 6),
            ]);

        $totalCalls = (int) ($totals->total_calls ?? 0);

        return response()->json([
            'total_calls' => $totalCalls,
            'successful_calls' => (int) ($totals->successful_calls ?? 0),
            'failed_calls' => (int) ($totals->failed_calls ?? 0),
            'success_rate' => $totalCalls > 0
                ? round(((int) ($totals->successful_calls ?? 0)) / $totalCalls * 100, 1)
                : 100.0,
            'total_tokens' => (int) ($totals->total_tokens ?? 0),
            'total_input_tokens' => (int) ($totals->total_input_tokens ?? 0),
            'total_output_tokens' => (int) ($totals->total_output_tokens ?? 0),
            'total_cost_usd' => round((float) ($totals->total_cost_usd ?? 0), 6),
            'avg_duration_ms' => round((float) ($totals->avg_duration_ms ?? 0), 0),
            'by_action' => $byAction,
            'by_model' => $byModel,
        ]);
    }
}
