<?php

namespace App\Http\Requests\Identity;

use Illuminate\Foundation\Http\FormRequest;

class SyncUserTeamsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_ids' => ['array'],
            'team_ids.*' => ['exists:teams,id'],
        ];
    }
}
