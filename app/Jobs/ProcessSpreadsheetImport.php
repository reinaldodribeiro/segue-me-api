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

        if ($rows->isEmpty()) {
            Cache::put($this->cacheKey, [
                'status' => 'done',
                'imported' => 0,
                'errors' => [],
            ], now()->addHours(2));

            return;
        }

        // Extract headers from first row, normalize to snake_case keys
        $headers = $rows->first()->map(fn ($h) => Str::slug(trim((string) $h), '_'))->toArray();

        $imported = 0;
        $errors = [];
        $toInsert = [];
        $now = now()->toDateTimeString();

        $splitCsv = fn (?string $raw): array => $raw ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $raw)))) : [];

        // ---------------------------------------------------------------
        // Pass 1: parse every data row into a structured array so we can
        // run duplicate detection in a single batch query instead of one
        // query per row.
        // ---------------------------------------------------------------
        $parsedRows = [];   // index => ['line', 'data', 'get', ...parsed fields]
        foreach ($rows->skip(1) as $lineNumber => $row) {
            $line = $lineNumber + 2;
            $data = [];
            foreach ($headers as $i => $key) {
                $data[$key] = trim((string) ($row[$i] ?? ''));
            }
            $get = fn (string $key): ?string => ($data[$key] ?? '') !== '' ? $data[$key] : null;

            $parsedRows[$lineNumber] = [
                'line' => $line,
                'get' => $get,
                'name' => $get('nome'),
                'type' => strtolower($get('tipo') ?? ''),
                'phone' => $get('telefone'),
                'email' => $get('email'),
                'birth_date' => self::parseDate($get('data_nascimento') ?? ''),
            ];
        }

        // ---------------------------------------------------------------
        // Primary deduplication (name + birth_date): one query per unique
        // birth_date group — much cheaper than per-row.
        // ---------------------------------------------------------------
        $birthDateDuplicates = []; // lineNumber => true
        $birthDateRows = array_filter($parsedRows, fn ($r) => ! empty($r['birth_date']) && ! empty($r['name']));
        if (! empty($birthDateRows)) {
            // Build pairs for a single IN-style query
            foreach ($birthDateRows as $lineNumber => $r) {
                $exists = Person::withoutGlobalScopes()
                    ->where('parish_id', $this->parishId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($r['name'])])
                    ->where('birth_date', $r['birth_date'])
                    ->exists();

                if ($exists) {
                    $birthDateDuplicates[$lineNumber] = true;
                }
            }
        }

        // ---------------------------------------------------------------
        // Fallback deduplication (email / phone / similar name): batch.
        // Only run for rows that didn't already hit a birth_date duplicate.
        // ---------------------------------------------------------------
        $fuzzyRows = [];
        foreach ($parsedRows as $lineNumber => $r) {
            if (isset($birthDateDuplicates[$lineNumber])) {
                continue;
            }
            if (empty($r['name'])) {
                continue;
            }
            $fuzzyRows[$lineNumber] = [
                'name' => $r['name'],
                'phone' => $r['phone'],
                'email' => $r['email'],
            ];
        }

        $batchDuplicates = $detect->executeBatch($fuzzyRows, $this->parishId);

        // ---------------------------------------------------------------
        // Pass 2: apply validation results and build insert list.
        // ---------------------------------------------------------------
        foreach ($parsedRows as $lineNumber => $r) {
            $line = $r['line'];
            $get = $r['get'];
            $name = $r['name'];
            $type = $r['type'];

            try {
                if (empty($name)) {
                    $errors[] = ['linha' => $line, 'motivo' => 'Nome obrigatório.'];

                    continue;
                }

                if (! in_array($type, ['jovem', 'youth', 'casal', 'couple'])) {
                    $errors[] = ['linha' => $line, 'motivo' => 'Tipo inválido. Use: jovem ou casal.'];

                    continue;
                }

                $personType = in_array($type, ['casal', 'couple']) ? 'couple' : 'youth';

                $phoneRaw = $r['phone'];
                $phones = $phoneRaw ? [$phoneRaw] : null;
                $email = $r['email'];
                $birthDate = $r['birth_date'];

                // Primary deduplication result
                if (isset($birthDateDuplicates[$lineNumber])) {
                    $errors[] = ['linha' => $line, 'motivo' => 'Duplicata: já existe uma pessoa com o mesmo nome e data de nascimento.'];

                    continue;
                }

                // Fallback deduplication result (batch)
                $duplicates = $batchDuplicates[$lineNumber] ?? collect();
                if ($duplicates->isNotEmpty()) {
                    $errors[] = ['linha' => $line, 'motivo' => "Possível duplicata: {$duplicates->first()->name}."];

                    continue;
                }

                // Skills (CSV → array)
                $skills = $splitCsv($get('habilidades'));

                // Encounter year
                $rawEncounterYear = $get('ano_encontro');
                $encounterYear = $rawEncounterYear && is_numeric($rawEncounterYear)
                    ? (int) $rawEncounterYear
                    : null;

                // Sacraments (CSV → array, youth-only)
                $rawSacraments = $get('sacramentos');
                $sacraments = $splitCsv($rawSacraments);

                // Couple array fields
                $partnerPhones = $splitCsv($get('telefones_conjuge'));
                $homePhones = $splitCsv($get('telefones_residencial'));

                $toInsert[] = [
                    'id' => (string) Str::uuid(),
                    'parish_id' => $this->parishId,
                    'type' => $personType,
                    // Core fields
                    'name' => $name,
                    'nickname' => $get('apelido'),
                    'birth_date' => $birthDate,
                    'birthplace' => $get('naturalidade'),
                    'address' => $get('endereco'),
                    'phones' => $phones ? json_encode($phones) : null,
                    'email' => $email,
                    'church_movement' => $get('movimento_igreja'),
                    'received_at' => self::parseDate($get('data_recebimento') ?? ''),
                    'encounter_year' => $encounterYear,
                    'encounter_details' => $get('detalhes_encontro'),
                    'skills' => json_encode($skills),
                    'notes' => $get('observacoes'),
                    // Youth-only fields
                    'father_name' => $get('nome_pai'),
                    'mother_name' => $get('nome_mae'),
                    'education_level' => $get('nivel_educacao'),
                    'education_status' => $get('status_educacao'),
                    'course' => $get('curso'),
                    'institution' => $get('instituicao'),
                    'sacraments' => $sacraments ? json_encode($sacraments) : null,
                    'available_schedule' => $get('disponibilidade_horario'),
                    'musical_instruments' => $get('instrumentos_musicais'),
                    'talks_testimony' => $get('pregacoes_testemunhos'),
                    // Couple-only fields
                    'partner_name' => $get('nome_conjuge'),
                    'partner_nickname' => $get('apelido_conjuge'),
                    'partner_birth_date' => self::parseDate($get('data_nascimento_conjuge') ?? ''),
                    'partner_birthplace' => $get('naturalidade_conjuge'),
                    'partner_email' => $get('email_conjuge'),
                    'partner_phones' => $partnerPhones ? json_encode($partnerPhones) : null,
                    'wedding_date' => self::parseDate($get('data_casamento') ?? ''),
                    'home_phones' => $homePhones ? json_encode($homePhones) : null,
                    // Defaults
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

            // Recalculate engagement scores for all newly imported people in one batch job
            $createdPersonIds = array_column($toInsert, 'id');
            RecalculateEngagementScoreBatch::dispatch($createdPersonIds);
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
