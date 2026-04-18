<?php

namespace App\Http\Requests\Parish;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDioceseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SuperAdmin->value);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:500'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
