<?php

namespace App\Http\Requests\Identity;

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
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user)],
            'password' => ['sometimes', Password::defaults()],
            'department_ids' => ['array'],
            'department_ids.*' => ['exists:departments,id'],
            'team_ids' => ['array'],
            'team_ids.*' => ['exists:teams,id'],
            'is_active' => ['boolean'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ];
    }
}
