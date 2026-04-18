<?php

namespace App\Http\Requests\Parish;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value]);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
