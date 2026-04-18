<?php

namespace App\Http\Requests\People;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonTeamExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'movement_team_id' => ['nullable', 'uuid', 'exists:movement_teams,id'],
            'team_name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:coordinator,member'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
        ];
    }
}
