<?php

namespace App\Jobs;

use App\Domain\People\Actions\DetectDuplicates;
use App\Domain\People\Models\Person;
use App\Domain\People\Repositories\PersonRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProcessSpreadsheetImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(
        private readonly string $filePath,
        private readonly string $parishId,
        private readonly string $cacheKey,
    ) {}

    public function handle(PersonRepositoryInterface $people, DetectDuplicates $detect): void
    {
        $path = Storage::path($this->filePath);
        $rows = Excel::toCollection(null, $path)->first();

        $imported = 0;
        $errors = [];
        $toInsert = [];
        $now = now()->toDateTimeString();

        // Pula cabeçalho
        foreach ($rows->skip(1) as $lineNumber => $row) {
            $line = $lineNumber + 2;
            try {
                $name = trim((string) $row[0]);
                $type = strtolower(trim((string) $row[1]));

                if (empty($name)) {
                    $errors[] = ['linha' => $line, 'motivo' => 'Nome obrigatório.'];

                    continue;
                }

                if (! in_array($type, ['jovem', 'youth', 'casal', 'couple'])) {
                    $errors[] = ['linha' => $line, 'motivo' => 'Tipo inválido. Use: jovem ou casal.'];

                    continue;
                }

                $personType = in_array($type, ['casal', 'couple']) ? 'couple' : 'youth';
                $phoneRaw = trim((string) ($row[6] ?? '')) ?: null;
                $phones = $phoneRaw ? [$phoneRaw] : null;
                $email = trim((string) ($row[7] ?? '')) ?: null;

                // Columns: 0=nome, 1=tipo, 2=nome_conjuge, 3=data_nascimento,
                // 4=data_nascimento_conjuge, 5=data_casamento, 6=telefone,
                // 7=email, 8=habilidades, 9=ano_encontro, 10=observacoes

                $birthDate = self::parseDate(trim((string) ($row[3] ?? '')));
                $partnerBirthDate = self::parseDate(trim((string) ($row[4] ?? '')));
                $weddingDate = self::parseDate(trim((string) ($row[5] ?? '')));

                $rawEncounterYear = trim((string) ($row[9] ?? ''));
                $encounterYear = $rawEncounterYear && is_numeric($rawEncounterYear)
                    ? (int) $rawEncounterYear
                    : null;

                // Primary deduplication: name + birth_date
                if ($birthDate) {
                    $exists = Person::where('parish_id', $this->parishId)
                        ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                        ->where('birth_date', $birthDate)
                        ->exists();

                    if ($exists) {
                        $errors[] = ['linha' => $line, 'motivo' => 'Duplicata: já existe uma pessoa com o mesmo nome e data de nascimento.'];

                        continue;
                    }
                }

                // Fallback deduplication: email / phone
                $duplicates = $detect->execute($name, $phoneRaw, $email, $this->parishId);
                if ($duplicates->isNotEmpty()) {
                    $errors[] = ['linha' => $line, 'motivo' => "Possível duplicata: {$duplicates->first()->name}."];

                    continue;
                }

                $rawSkills = trim((string) ($row[8] ?? ''));
                $skills = $rawSkills
                    ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $rawSkills))))
                    : [];

                $toInsert[] = [
                    'id' => (string) Str::uuid(),
                    'parish_id' => $this->parishId,
                    'type' => $personType,
                    'name' => $name,
                    'partner_name' => trim((string) ($row[2] ?? '')) ?: null,
                    'birth_date' => $birthDate,
                    'partner_birth_date' => $partnerBirthDate,
                    'wedding_date' => $weddingDate,
                    'phones' => $phones ? json_encode($phones) : null,
                    'email' => $email,
                    'skills' => json_encode($skills),
                    'encounter_year' => $encounterYear,
                    'notes' => trim((string) ($row[10] ?? '')) ?: null,
                    'active' => true,
                    'engagement_score' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $imported++;
            } catch (\Throwable $e) {
                $errors[] = ['linha' => $line, 'motivo' => 'Erro inesperado: '.$e->getMessage()];
            }
        }

        if (! empty($toInsert)) {
            $people->insertMany($toInsert);
        }

        Cache::put($this->cacheKey, [
            'status' => 'done',
            'imported' => $imported,
            'errors' => $errors,
        ], now()->addHours(2));
    }

    private static function parseDate(string $raw): ?string
    {
        if (! $raw) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d/m/Y', $raw)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function failed(\Throwable $e): void
    {
        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'message' => 'Falha ao processar a planilha.',
        ], now()->addHours(2));
    }
}
