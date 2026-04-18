<?php

namespace App\Domain\Parish\Models;

use App\Models\User;
use App\Support\Traits\HasUuid;
use Database\Factories\ParishFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parish extends Model
{
    /** @use HasFactory<ParishFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'sector_id',
        'name',
        'slug',
        'logo',
        'primary_color',
        'secondary_color',
        'available_skills',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'available_skills' => 'array',
        ];
    }

    protected static function newFactory(): ParishFactory
    {
        return ParishFactory::new();
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
