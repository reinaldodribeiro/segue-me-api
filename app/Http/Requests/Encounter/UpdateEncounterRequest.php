<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\EncounterStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value, UserRole::Coordinator->value]);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'responsible_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
            'duration_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(EncounterStatus::class)],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
