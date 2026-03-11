<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'department_ids' => ['array'],
            'department_ids.*' => ['exists:departments,id'],
            'team_ids' => ['array'],
            'team_ids.*' => ['exists:teams,id'],
            'is_active' => ['boolean'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ];
    }
}
