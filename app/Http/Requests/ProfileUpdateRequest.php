<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'first_name' => [$this->user()->isStudent() ? 'required' : 'nullable', 'string', 'max:120'],
            'last_name' => [$this->user()->isStudent() ? 'required' : 'nullable', 'string', 'max:120'],
            'preferred_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
