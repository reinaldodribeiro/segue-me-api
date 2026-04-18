<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\Exceptions\ClaudeApiException;
use App\Domain\AI\Models\AiApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private string $model;

    private const API_URL = 'https://api.anthropic.com/v1/messages';

    private const API_VERSION = '2023-06-01';

    /**
     * Approximate cost per million tokens (USD) by model prefix.
     * Input / Output prices.
     */
    private const MODEL_PRICING = [
        'claude-opus' => [15.00, 75.00],
        'claude-sonnet' => [3.00,  15.00],
        'claude-haiku' => [0.80,   4.00],
    ];

    public function __construct()
    {
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-6');
    }

    public function complete(
        string $prompt,
        array $images = [],
        ?string $model = null,
        string $action = '',
        array $metadata = [],
        int $timeout = 60,
        int $maxTokens = 8192,
    ): string {
        $content = [];

        foreach ($images as $image) {
            $content[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $image['type'],
                    'data' => $image['data'],
                ],
            ];
        }

        $content[] = ['type' => 'text', 'text' => $prompt];

        $usedModel = $model ?? $this->model;
        $startedAt = hrtime(true);

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => self::API_VERSION,
            'content-type' => 'application/json',
        ])
            ->timeout($timeout)
            ->post(self::API_URL, [
                'model' => $usedModel,
                'max_tokens' => $maxTokens,
                'messages' => [['role' => 'user', 'content' => $content]],
            ]);

        $durationMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        if (! $response->successful()) {
            $this->writeLog(
                action: $action,
                model: $usedModel,
                inputTokens: 0,
                outputTokens: 0,
                success: false,
                errorMessage: $response->body(),
                durationMs: $durationMs,
                metadata: $metadata,
            );

            throw new ClaudeApiException('Claude API error: '.$response->body());
        }

        $usage = $response->json('usage') ?? [];
        $inputTokens = (int) ($usage['input_tokens'] ?? 0);
        $outputTokens = (int) ($usage['output_tokens'] ?? 0);

        $this->writeLog(
            action: $action,
            model: $usedModel,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            success: true,
            errorMessage: null,
            durationMs: $durationMs,
            metadata: $metadata,
        );

        return $response->json('content.0.text');
    }

    public function completeAsJson(
        string $prompt,
        array $images = [],
        ?string $model = null,
        string $action = '',
        array $metadata = [],
        int $timeout = 60,
        int $maxTokens = 8192,
    ): array {
        $text = $this->complete($prompt, $images, $model, $action, $metadata, $timeout, $maxTokens);
        $clean = preg_replace('/^\s*```json\s*|\s*```\s*$/m', '', trim($text));
        $clean = preg_replace('/[\x00-\x1F\x7F]/', '', $clean);
        $clean = mb_convert_encoding($clean, 'UTF-8', 'UTF-8');

        try {
            return json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('ClaudeService: JSON decode failed', [
                'error' => $e->getMessage(),
                'raw' => substr($text, 0, 500),
            ]);

            throw $e;
        }
    }

    private function writeLog(
        string $action,
        string $model,
        int $inputTokens,
        int $outputTokens,
        bool $success,
        ?string $errorMessage,
        int $durationMs,
        array $metadata,
    ): void {
        try {
            $cost = $this->estimateCost($model, $inputTokens, $outputTokens);

            AiApiLog::create([
                'user_id' => auth()->id(),
                'action' => $action ?: 'unknown',
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $inputTokens + $outputTokens,
                'estimated_cost_usd' => $cost,
                'success' => $success,
                'error_message' => $errorMessage,
                'duration_ms' => $durationMs,
                'metadata' => $metadata ?: null,
            ]);
        } catch (\Throwable $e) {
            // Never let logging break the main flow
            Log::warning('ClaudeService: failed to write AI log', ['error' => $e->getMessage()]);
        }
    }

    private function estimateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        [$inputPrice, $outputPrice] = $this->getPricing($model);

        return round(
            ($inputTokens / 1_000_000) * $inputPrice +
            ($outputTokens / 1_000_000) * $outputPrice,
            8,
        );
    }

    private function getPricing(string $model): array
    {
        foreach (self::MODEL_PRICING as $prefix => $prices) {
            if (str_contains($model, $prefix)) {
                return $prices;
            }
        }

        // Default to sonnet pricing if unknown
        return self::MODEL_PRICING['claude-sonnet'];
    }
}
