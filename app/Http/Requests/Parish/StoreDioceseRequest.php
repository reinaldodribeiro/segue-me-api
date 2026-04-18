<?php

namespace App\Http\Requests\Parish;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreDioceseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SuperAdmin->value);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', 'unique:dioceses,slug'],
            'logo' => ['nullable', 'string', 'max:500'],
        ];
    }
}
