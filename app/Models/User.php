<?php

namespace App\Models;

use App\Domain\Encounter\Models\Movement;
use App\Domain\Parish\Models\Diocese;
use App\Domain\Parish\Models\Parish;
use App\Domain\Parish\Models\Sector;
use App\Support\Enums\UserRole;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'parish_id',
        'sector_id',
        'diocese_id',
        'name',
        'email',
        'password',
        'active',
        'tutorial_seen',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'tutorial_seen' => 'array',
        ];
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class);
    }

    public function movements(): BelongsToMany
    {
        return $this->belongsToMany(
            Movement::class,
            'movement_user',
        )->withTimestamps();
    }

    /**
     * Returns the movement IDs this user can access.
     * Coordinators: only assigned movements. Others: all movements.
     */
    public function accessibleMovementIds(): ?array
    {
        if ($this->hasRole(UserRole::Coordinator->value)) {
            return DB::table('movement_user')
                ->where('user_id', $this->id)
                ->pluck('movement_id')
                ->toArray();
        }

        // Non-coordinators see everything (no filter needed)
        return null;
    }

    public function roleName(): string
    {
        return $this->roles->first()?->name ?? '';
    }
}
