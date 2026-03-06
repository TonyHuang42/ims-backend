<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreUserRequest;
use App\Http\Requests\Identity\SyncUserRolesRequest;
use App\Http\Requests\Identity\UpdateUserRequest;
use App\Http\Resources\Identity\RoleResource;
use App\Http\Resources\Identity\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()->with(['department', 'team', 'roles']);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return UserResource::collection($query->paginate($perPage));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($request->has('role_ids')) {
            $user->roles()->sync($request->role_ids);
        }

        $resource = new UserResource($user->load(['department', 'team', 'roles']));

        return response()->json($resource->response()->getData(), 201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['department', 'team', 'roles']));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        $resource = new UserResource($user->load(['department', 'team', 'roles']));

        return response()->json($resource->response()->getData(), 200);
    }

    public function destroy(User $user): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User soft-deleted successfully']);
    }

    public function syncRoles(SyncUserRolesRequest $request, User $user): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $user->roles()->sync($request->role_ids);

        return response()->json(['message' => 'Roles synced successfully']);
    }

    public function getRoles(User $user): AnonymousResourceCollection
    {
        return RoleResource::collection($user->roles);
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
