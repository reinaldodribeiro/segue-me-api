<?php

namespace App\Domain\People\Actions;

use App\Domain\People\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DetectDuplicates
{
    public function execute(string $name, ?string $phone, ?string $email, string $parishId): Collection
    {
        $query = Person::withoutGlobalScopes()
            ->where('parish_id', $parishId);

        return $query->get()->filter(function (Person $person) use ($name, $phone, $email) {
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
        })->values();
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
