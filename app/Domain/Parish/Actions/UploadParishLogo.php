<?php

namespace App\Domain\Parish\Actions;

use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Repositories\ParishRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadParishLogo
{
    public function __construct(
        private readonly ParishRepositoryInterface $parishes,
    ) {}

    public function execute(Parish $parish, UploadedFile $file): Parish
    {
        if ($parish->logo) {
            Storage::disk('public')->delete($parish->logo);
        }

        $path = $file->store("parishes/{$parish->id}/logo", 'public');

        $this->parishes->update($parish, ['logo' => $path]);

        return $parish->refresh();
    }
}
