<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\TeamAcceptedType;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['required', 'integer', 'min:1', 'gte:min_members'],
            'coordinators_youth' => ['nullable', 'integer', 'min:0'],
            'coordinators_couples' => ['nullable', 'integer', 'min:0'],
            'accepted_type' => ['required', Rule::enum(TeamAcceptedType::class)],
            'recommended_skills' => ['nullable', 'array'],
            'recommended_skills.*' => ['string', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da equipe é obrigatório.',
            'min_members.required' => 'O mínimo de membros é obrigatório.',
            'max_members.required' => 'O máximo de membros é obrigatório.',
            'max_members.gte' => 'O máximo deve ser maior ou igual ao mínimo.',
            'accepted_type.required' => 'O tipo aceito é obrigatório.',
        ];
    }
}
