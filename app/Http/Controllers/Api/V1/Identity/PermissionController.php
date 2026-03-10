<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StorePermissionRequest;
use App\Http\Requests\Identity\UpdatePermissionRequest;
use App\Http\Resources\Identity\PermissionResource;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Permission::query();

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

        return PermissionResource::collection($query->paginate($perPage));
    }

    public function store(StorePermissionRequest $request): PermissionResource|JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $permission = Permission::create($request->validated());

        return new PermissionResource($permission);
    }

    public function show(Permission $permission): PermissionResource
    {
        return new PermissionResource($permission);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): PermissionResource|JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $permission->update($request->validated());

        return new PermissionResource($permission);
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
