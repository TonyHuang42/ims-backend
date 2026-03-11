<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('api')->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'is_active' => ['boolean'],
        ];
    }
}
