<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreRoleRequest;
use App\Http\Requests\Identity\SyncRolePermissionsRequest;
use App\Http\Requests\Identity\UpdateRoleRequest;
use App\Http\Resources\Identity\PermissionResource;
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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
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

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        return new RoleResource($role->load('permissions'));
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->load('permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource|JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $role->update($request->validated());

        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        return new RoleResource($role->load('permissions'));
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $role->permissions()->sync($request->permission_ids);

        return response()->json(['message' => 'Permissions synced successfully']);
    }

    public function getPermissions(Role $role): AnonymousResourceCollection
    {
        return PermissionResource::collection($role->permissions);
    }

    protected function isAdmin(): bool
    {
        $user = Auth::guard('api')->user();
        if (! $user instanceof User) {
            return false;
        }

        return $user->roles()->where('slug', 'admin')->exists();
    }
}
