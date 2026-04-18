<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value, UserRole::Coordinator->value]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['required', 'integer', 'min:1', 'gte:min_members'],
            'accepted_type' => ['required', Rule::enum(TeamAcceptedType::class)],
            'recommended_skills' => ['nullable', 'array'],
            'recommended_skills.*' => ['string', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
