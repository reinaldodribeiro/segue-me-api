<?php

namespace App\Domain\AI\Models;

use App\Models\User;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiApiLog extends Model
{
    use HasUuid;

    protected $table = 'ai_api_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'estimated_cost_usd',
        'success',
        'error_message',
        'duration_ms',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'total_tokens' => 'integer',
            'estimated_cost_usd' => 'float',
            'success' => 'boolean',
            'duration_ms' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
