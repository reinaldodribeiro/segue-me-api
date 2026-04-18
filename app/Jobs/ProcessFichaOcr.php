<?php

namespace App\Jobs;

use App\Domain\AI\Prompts\ExtractFichaPrompt;
use App\Domain\AI\Services\ClaudeService;
use App\Domain\People\Actions\DetectDuplicates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProcessFichaOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        private readonly string $filePath,
        private readonly string $parishId,
        private readonly string $requestedBy,
        private readonly string $cacheKey,
    ) {}

    public function handle(ClaudeService $claude, DetectDuplicates $detect): void
    {
        $file = Storage::get($this->filePath);
        $mimeType = Storage::mimeType($this->filePath);
        $base64 = base64_encode($file);

        $result = $claude->completeAsJson(
            ExtractFichaPrompt::build(),
            [['type' => $mimeType, 'data' => $base64]],
            null,
            'ficha_extraction',
            ['parish_id' => $this->parishId],
        );

        $result['parish_id'] = $this->parishId;

        $duplicates = $detect->execute(
            $result['name'] ?? '',
            $result['phones'][0] ?? null,
            $result['email'] ?? null,
            $this->parishId,
        );

        Cache::put($this->cacheKey, [
            'status' => 'done',
            'data' => $result,
            'potential_duplicates' => $duplicates->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values(),
        ], now()->addHours(2));
    }

    public function failed(\Throwable $e): void
    {
        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'message' => 'Falha ao processar a ficha. Faça o cadastro manualmente.',
        ], now()->addHours(2));
    }
}
