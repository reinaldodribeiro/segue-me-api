<?php

namespace App\Domain\Encounter\Models;

use App\Domain\People\Models\Person;
use App\Support\Enums\PersonType;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterParticipant extends Model
{
    use HasUuid;

    protected $fillable = [
        'encounter_id',
        'name',
        'partner_name',
        'type',
        'phone',
        'email',
        'birth_date',
        'partner_birth_date',
        'photo',
        'converted_to_person_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => PersonType::class,
            'birth_date' => 'date',
            'partner_birth_date' => 'date',
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function convertedToPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'converted_to_person_id');
    }

    public function isConverted(): bool
    {
        return $this->converted_to_person_id !== null;
    }
}
