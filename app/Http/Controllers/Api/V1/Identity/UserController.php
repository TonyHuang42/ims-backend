<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreUserRequest;
use App\Http\Requests\Identity\SyncUserDepartmentsRequest;
use App\Http\Requests\Identity\SyncUserTeamsRequest;
use App\Http\Requests\Identity\UpdateUserRequest;
use App\Http\Resources\Identity\DepartmentResource;
use App\Http\Resources\Identity\TeamResource;
use App\Http\Resources\Identity\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()->with(['departments', 'teams', 'role']);

        if ($request->has('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        if ($request->has('team_id')) {
            $query->whereHas('teams', function ($q) use ($request) {
                $q->where('teams.id', $request->team_id);
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
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
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if ($request->has('department_ids')) {
            $user->departments()->sync($request->department_ids);
        }

        if ($request->has('team_ids')) {
            $user->teams()->sync($request->team_ids);
        }

        $resource = new UserResource($user->load(['departments', 'teams', 'role']));

        return response()->json($resource->response()->getData(), 201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['departments', 'teams', 'role']));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        if ($request->has('department_ids')) {
            $user->departments()->sync($request->department_ids);
        }

        if ($request->has('team_ids')) {
            $user->teams()->sync($request->team_ids);
        }

        $resource = new UserResource($user->load(['departments', 'teams', 'role']));

        return response()->json($resource->response()->getData(), 200);
    }

    public function syncDepartments(SyncUserDepartmentsRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->departments()->sync($request->department_ids);

        return response()->json(['message' => 'Departments synced successfully']);
    }

    public function getDepartments(User $user): AnonymousResourceCollection
    {
        return DepartmentResource::collection($user->departments);
    }

    public function syncTeams(SyncUserTeamsRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->teams()->sync($request->team_ids);

        return response()->json(['message' => 'Teams synced successfully']);
    }

    public function getTeams(User $user): AnonymousResourceCollection
    {
        return TeamResource::collection($user->teams);
    }
}
