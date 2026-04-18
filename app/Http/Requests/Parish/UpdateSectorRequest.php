<?php

namespace App\Http\Requests\Parish;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SuperAdmin->value);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
