<?php

namespace App\Http\Requests\Auth;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'parish_id' => ['nullable', 'uuid', 'exists:parishes,id'],
            'sector_id' => ['nullable', 'uuid', 'exists:sectors,id'],
            'diocese_id' => ['nullable', 'uuid', 'exists:dioceses,id'],
            'active' => ['boolean'],
        ];
    }
}
