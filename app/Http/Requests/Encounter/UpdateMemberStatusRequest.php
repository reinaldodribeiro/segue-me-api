<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\TeamMemberStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value, UserRole::Coordinator->value]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TeamMemberStatus::class)],
            'refusal_reason' => ['nullable', 'string', 'max:500', Rule::requiredIf(
                fn () => $this->input('status') === TeamMemberStatus::Refused->value
            )],
        ];
    }
}
