<?php

namespace App\Http\Requests\Encounter;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class AllocatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value, UserRole::Coordinator->value]);
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', 'uuid', 'exists:people,id'],
            'role' => ['nullable', 'string', 'in:coordinator,member'],
        ];
    }
}
