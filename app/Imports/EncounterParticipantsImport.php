<?php

namespace App\Imports;

use App\Domain\Encounter\Models\EncounterParticipant;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EncounterParticipantsImport implements SkipsOnError, ToModel, WithHeadingRow
{
    use SkipsErrors;

    private int $imported = 0;

    public function __construct(
        private readonly string $encounterId,
    ) {}

    /**
     * Accepted heading columns (case-insensitive, trimmed):
     * nome | name
     * nome_conjuge | partner_name | conjuge (optional)
     * tipo | type  (jovem/casal | youth/couple — defaults to youth)
     * telefone | phone (optional)
     * email (optional)
     * nascimento | birth_date (optional, d/m/Y or Y-m-d)
     */
    public function model(array $row): ?EncounterParticipant
    {
        $name = trim($row['nome'] ?? $row['name'] ?? '');
        if (empty($name)) {
            return null;
        }

        $typeRaw = strtolower(trim($row['tipo'] ?? $row['type'] ?? 'jovem'));
        $type = in_array($typeRaw, ['casal', 'couple']) ? 'couple' : 'youth';

        $birthDate = $this->parseDate($row['nascimento'] ?? $row['birth_date'] ?? null);

        // Deduplicate by name + birth_date within the same encounter
        $query = EncounterParticipant::where('encounter_id', $this->encounterId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)]);

        if ($birthDate) {
            $query->where('birth_date', $birthDate);
        }

        if ($query->exists()) {
            return null;
        }

        $this->imported++;

        return new EncounterParticipant([
            'encounter_id' => $this->encounterId,
            'name' => $name,
            'partner_name' => trim($row['nome_conjuge'] ?? $row['partner_name'] ?? $row['conjuge'] ?? '') ?: null,
            'type' => $type,
            'phone' => trim($row['telefone'] ?? $row['phone'] ?? '') ?: null,
            'email' => strtolower(trim($row['email'] ?? '')) ?: null,
            'birth_date' => $birthDate,
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // d/m/Y → Y-m-d
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', trim($value), $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        // Y-m-d passthrough
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value))) {
            return trim($value);
        }

        return null;
    }
}
