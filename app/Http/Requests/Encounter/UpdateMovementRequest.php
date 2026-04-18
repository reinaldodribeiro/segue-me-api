<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\MovementScope;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_audience' => ['required', Rule::enum(TeamAcceptedType::class)],
            'scope' => ['required', Rule::enum(MovementScope::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
