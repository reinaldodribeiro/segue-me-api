<?php

namespace App\Support;

use Illuminate\Support\Str;

class CacheKey
{
    public static function narrativeBase(string $encounterId): string
    {
        return sprintf('narrative:%s', $encounterId);
    }

    public static function narrative(string $encounterId): string
    {
        return sprintf('narrative:%s:%s', $encounterId, Str::uuid());
    }

    public static function teamSuggestions(string $teamId, string $role = 'member'): string
    {
        return sprintf('team.suggestions:%s:%s', $teamId, $role);
    }

    public static function replacementSuggestions(string $teamId): string
    {
        return sprintf('team.replacement-suggestions:%s', $teamId);
    }

    public static function spreadsheetImport(): string
    {
        return 'import:spreadsheet:'.Str::uuid();
    }

    public static function ocrImport(): string
    {
        return 'import:ocr:'.Str::uuid();
    }
}
