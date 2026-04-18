<?php

namespace App\Http\Requests\Encounter;

use Illuminate\Foundation\Http\FormRequest;

class SubmitEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public route — authorization is via token + session
    }

    public function rules(): array
    {
        return [
            'session_token' => ['required', 'string'],

            // General team questions
            'preparation_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'preparation_comment' => ['nullable', 'string', 'max:2000'],
            'teamwork_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'teamwork_comment' => ['nullable', 'string', 'max:2000'],
            'materials_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'materials_comment' => ['nullable', 'string', 'max:2000'],
            'issues_text' => ['nullable', 'string', 'max:5000'],
            'improvements_text' => ['nullable', 'string', 'max:5000'],
            'overall_team_rating' => ['required', 'integer', 'min:1', 'max:5'],

            // Individual member evaluations
            'members' => ['required', 'array', 'min:1'],
            'members.*.team_member_id' => ['required', 'uuid', 'exists:team_members,id'],
            'members.*.commitment_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'members.*.fulfilled_responsibilities' => ['required', 'string', 'in:yes,partially,no'],
            'members.*.positive_highlight' => ['nullable', 'string', 'max:2000'],
            'members.*.issue_observed' => ['nullable', 'string', 'max:2000'],
            'members.*.recommend' => ['required', 'string', 'in:yes,with_reservations,no'],
        ];
    }

    public function messages(): array
    {
        return [
            'preparation_rating.required' => 'A nota de preparação é obrigatória.',
            'teamwork_rating.required' => 'A nota de trabalho em equipe é obrigatória.',
            'materials_rating.required' => 'A nota de materiais é obrigatória.',
            'overall_team_rating.required' => 'A nota geral da equipe é obrigatória.',
            'members.required' => 'A avaliação individual dos membros é obrigatória.',
            'members.*.commitment_rating.required' => 'A nota de comprometimento é obrigatória.',
            'members.*.fulfilled_responsibilities.required' => 'O campo de responsabilidades é obrigatório.',
            'members.*.recommend.required' => 'A recomendação é obrigatória.',
        ];
    }
}
