<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreRoleRequest;
use App\Http\Requests\Identity\UpdateRoleRequest;
use App\Http\Resources\Identity\RoleResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return RoleResource::collection($query->paginate($perPage));
    }

    public function store(StoreRoleRequest $request): RoleResource|JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $role = Role::create($request->validated());

        return new RoleResource($role);
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource|JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $role->update($request->validated());

        return new RoleResource($role);
    }

    protected function isAdmin(): bool
    {
        $user = Auth::guard('api')->user();
        if (! $user instanceof User) {
            return false;
        }

        return $user->role?->name === 'admin';
    }
}
