<?php

namespace App\Domain\Encounter\Models;

use App\Models\User;
use App\Support\Enums\MovementScope;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Traits\HasUuid;
use Database\Factories\MovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movement extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'target_audience',
        'scope',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'scope' => MovementScope::class,
            'target_audience' => TeamAcceptedType::class,
            'active' => 'boolean',
        ];
    }

    protected static function newFactory(): MovementFactory
    {
        return MovementFactory::new();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'movement_user',
        )->withTimestamps();
    }

    public function movementTeams(): HasMany
    {
        return $this->hasMany(MovementTeam::class)->orderBy('order');
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function nextEditionNumber(): int
    {
        return ($this->encounters()->max('edition_number') ?? 0) + 1;
    }
}
