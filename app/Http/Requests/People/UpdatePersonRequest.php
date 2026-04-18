<?php

namespace App\Http\Requests\People;

use App\Support\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole([UserRole::SuperAdmin->value, UserRole::ParishAdmin->value, UserRole::Coordinator->value]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'partner_birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'wedding_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'email' => ['nullable', 'email', 'max:255'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'active' => ['nullable', 'boolean'],
            'encounter_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            // Common new fields
            'nickname' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'phones' => ['nullable', 'array', 'max:4'],
            'phones.*' => ['string', 'max:20'],
            'church_movement' => ['nullable', 'string', 'max:1000'],
            'received_at' => ['nullable', 'date_format:Y-m-d'],
            'encounter_details' => ['nullable', 'string', 'max:1000'],
            // Youth-specific fields
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'education_status' => ['nullable', 'string', 'max:50'],
            'course' => ['nullable', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'sacraments' => ['nullable', 'array'],
            'sacraments.*' => ['string', 'in:batismo,eucaristia,crisma'],
            'available_schedule' => ['nullable', 'string', 'max:500'],
            'musical_instruments' => ['nullable', 'string', 'max:500'],
            'talks_testimony' => ['nullable', 'string', 'max:2000'],
            // Couple-specific fields
            'partner_nickname' => ['nullable', 'string', 'max:100'],
            'partner_birthplace' => ['nullable', 'string', 'max:255'],
            'partner_email' => ['nullable', 'email', 'max:255'],
            'partner_phones' => ['nullable', 'array', 'max:2'],
            'partner_phones.*' => ['string', 'max:20'],
            'partner_photo' => ['nullable', 'string', 'max:255'],
            'home_phones' => ['nullable', 'array', 'max:2'],
            'home_phones.*' => ['string', 'max:20'],
        ];
    }
}
