<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Policies\DepartmentPolicy;
use App\Policies\FormTemplatePolicy;
use App\Policies\RolePolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\Services\Identity\IdentityService;
use App\Services\Identity\IdentityServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IdentityServiceInterface::class, IdentityService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(FormTemplate::class, FormTemplatePolicy::class);
    }
}
