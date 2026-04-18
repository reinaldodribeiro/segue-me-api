<?php

namespace App\Domain\Encounter\Actions;

use App\Domain\Encounter\Models\EncounterParticipant;
use App\Domain\Encounter\Repositories\EncounterParticipantRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateEncounterParticipantPhoto
{
    public function __construct(
        private readonly EncounterParticipantRepositoryInterface $repo,
    ) {}

    public function execute(
        EncounterParticipant $participant,
        UploadedFile $file,
    ): EncounterParticipant {
        $oldPhoto = $participant->photo;

        // Store new file first (outside transaction — filesystem I/O)
        $path = $file->store('participants/photos', 'public');

        try {
            $updated = DB::transaction(fn () => $this->repo->updatePhoto($participant, $path));
        } catch (\Throwable $e) {
            // Rollback: remove newly uploaded file if DB transaction fails
            Storage::disk('public')->delete($path);

            throw $e;
        }

        // Remove old photo only after successful DB commit
        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return $updated;
    }
}
