<?php

namespace App\Domain\Parish\Models;

use App\Support\Traits\HasUuid;
use Database\Factories\DioceseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diocese extends Model
{
    /** @use HasFactory<DioceseFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    protected static function newFactory(): DioceseFactory
    {
        return DioceseFactory::new();
    }

    public function sectors(): HasMany
    {
        return $this->hasMany(Sector::class);
    }
}
