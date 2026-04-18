<?php

namespace App\Http\Requests\Auth;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', Password::min(8)->letters()->numbers()],
            'role' => ['sometimes', Rule::in(array_column(UserRole::cases(), 'value'))],
            'parish_id' => ['nullable', 'uuid', 'exists:parishes,id'],
            'sector_id' => ['nullable', 'uuid', 'exists:sectors,id'],
            'diocese_id' => ['nullable', 'uuid', 'exists:dioceses,id'],
            'active' => ['boolean'],
        ];
    }
}
