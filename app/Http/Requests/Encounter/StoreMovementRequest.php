<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\MovementScope;
use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do movimento é obrigatório.',
            'target_audience.required' => 'O público-alvo é obrigatório.',
            'scope.required' => 'O âmbito é obrigatório.',
        ];
    }
}
