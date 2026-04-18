<?php

namespace App\Domain\Audit;

use App\Domain\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function __construct(private readonly Request $request) {}

    public function log(
        string $action,
        string $description,
        ?Model $model = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $this->request->user()?->id,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'description' => $description,
            'metadata' => $metadata ?: null,
            'ip_address' => $this->request->ip(),
            'created_at' => now(),
        ]);
    }
}
