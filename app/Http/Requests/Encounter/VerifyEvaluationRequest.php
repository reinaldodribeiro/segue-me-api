<?php

namespace App\Http\Requests\Encounter;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public route
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'size:4'],
        ];
    }
}
