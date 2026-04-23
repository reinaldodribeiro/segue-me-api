<?php

namespace App\Domain\People\Actions;

use App\Domain\People\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DetectDuplicates
{
    /**
     * Detect potential duplicate people for a single candidate.
     *
     * Uses a SQL WHERE clause to narrow candidates to a small set first,
     * then applies fine-grained PHP similarity only on that small set.
     * This avoids loading the entire parish collection into memory.
     */
    public function execute(string $name, ?string $phone, ?string $email, string $parishId): Collection
    {
        $candidates = $this->queryCandidates($name, $phone, $email, $parishId);

        return $candidates->filter(function (Person $person) use ($name, $phone, $email) {
            return $this->isMatch($person, $name, $phone, $email);
        })->values();
    }

    /**
     * Batch-detect duplicates for multiple import rows in a single query.
     *
     * @param  array<int, array{name: string, phone: ?string, email: ?string}>  $rows
     * @return array<int, Collection> Keyed by the same index as $rows
     */
    public function executeBatch(array $rows, string $parishId): array
    {
        if (empty($rows)) {
            return [];
        }

        $candidates = $this->queryCandidatesForBatch($rows, $parishId);

        $results = [];
        foreach ($rows as $index => $row) {
            $results[$index] = $candidates->filter(function (Person $person) use ($row) {
                return $this->isMatch($person, $row['name'], $row['phone'] ?? null, $row['email'] ?? null);
            })->values();
        }

        return $results;
    }

    /**
     * Query SQL candidates for a single row using broad WHERE filters.
     * Returns at most a small set for fine-grained comparison.
     */
    private function queryCandidates(string $name, ?string $phone, ?string $email, string $parishId): Collection
    {
        $prefix = $this->namePrefix($name);

        $query = Person::withoutGlobalScopes()
            ->where('parish_id', $parishId)
            ->where(function ($q) use ($prefix, $phone, $email) {
                // Name prefix match (broad filter for similar_text candidates)
                $q->whereRaw('LOWER(name) LIKE ?', ["{$prefix}%"]);

                // OR exact email match
                if ($email) {
                    $q->orWhereRaw('LOWER(email) = ?', [strtolower($email)]);
                }

                // OR phone substring match (digits only comparison done in PHP)
                if ($phone) {
                    $normalizedPhone = $this->normalizePhone($phone);
                    if ($normalizedPhone !== '') {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phones, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE ?", ["%{$normalizedPhone}%"]);
                    }
                }
            });

        return $query->get();
    }

    /**
     * Query SQL candidates for multiple rows in a single round-trip.
     */
    private function queryCandidatesForBatch(array $rows, string $parishId): Collection
    {
        $prefixes = [];
        $emails = [];
        $normalizedPhones = [];

        foreach ($rows as $row) {
            $prefixes[] = $this->namePrefix($row['name']).'%';

            if (! empty($row['email'])) {
                $emails[] = strtolower($row['email']);
            }

            if (! empty($row['phone'])) {
                $normalized = $this->normalizePhone($row['phone']);
                if ($normalized !== '') {
                    $normalizedPhones[] = $normalized;
                }
            }
        }

        $prefixes = array_unique($prefixes);
        $emails = array_unique($emails);

        $query = Person::withoutGlobalScopes()
            ->where('parish_id', $parishId)
            ->where(function ($q) use ($prefixes, $emails, $normalizedPhones) {
                // Any name prefix match
                $q->where(function ($inner) use ($prefixes) {
                    foreach ($prefixes as $prefix) {
                        $inner->orWhereRaw('LOWER(name) LIKE ?', [$prefix]);
                    }
                });

                // OR any exact email match
                if (! empty($emails)) {
                    $q->orWhereRaw('LOWER(email) IN ('.implode(',', array_fill(0, count($emails), '?')).')', $emails);
                }

                // OR any phone substring match
                foreach ($normalizedPhones as $phone) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phones, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE ?", ["%{$phone}%"]);
                }
            });

        return $query->get();
    }

    /**
     * Fine-grained match check applied to a pre-filtered candidate.
     */
    private function isMatch(Person $person, string $name, ?string $phone, ?string $email): bool
    {
        $nameSimilarity = $this->similarity($name, $person->name);

        if ($nameSimilarity < 85) {
            return false;
        }

        if ($phone && $person->phones) {
            $normalizedInput = $this->normalizePhone($phone);
            foreach ($person->phones as $personPhone) {
                if ($normalizedInput === $this->normalizePhone($personPhone)) {
                    return true;
                }
            }
        }

        if ($email && $person->email && strtolower($email) === strtolower($person->email)) {
            return true;
        }

        return false;
    }

    /**
     * Extract a normalised name prefix (first 4 chars, lower-case) for SQL LIKE filtering.
     * Short enough to cast a wide net; long enough to exclude unrelated names.
     */
    private function namePrefix(string $name): string
    {
        return Str::lower(Str::substr(trim($name), 0, 4));
    }

    private function similarity(string $a, string $b): float
    {
        similar_text(Str::lower($a), Str::lower($b), $percent);

        return $percent;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
