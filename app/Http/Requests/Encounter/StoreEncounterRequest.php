<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value, UserRole::Coordinator->value]);
    }

    public function rules(): array
    {
        return [
            'movement_id' => ['required', 'uuid', 'exists:movements,id'],
            'responsible_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'edition_number' => ['nullable', 'integer', 'min:1'],
            'date' => ['required', 'date_format:Y-m-d'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
