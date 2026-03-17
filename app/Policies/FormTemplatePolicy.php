<?php

namespace App\Policies;

use App\Models\FormTemplate;
use App\Models\User;

class FormTemplatePolicy
{
    public function view(User $user, FormTemplate $formTemplate): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $formTemplate->is_active;
    }

    public function viewInactive(User $user): bool
    {
        return $user->isAdmin();
    }
}
