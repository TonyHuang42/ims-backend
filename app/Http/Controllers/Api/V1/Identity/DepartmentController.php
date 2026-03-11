<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreDepartmentRequest;
use App\Http\Requests\Identity\UpdateDepartmentRequest;
use App\Http\Resources\Identity\DepartmentResource;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Department::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return DepartmentResource::collection($query->paginate($perPage));
    }

    public function store(StoreDepartmentRequest $request): DepartmentResource
    {
        $department = Department::create($request->validated());

        return new DepartmentResource($department);
    }

    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): DepartmentResource
    {
        $department->update($request->validated());

        return new DepartmentResource($department);
    }

    protected function isAdmin(): bool
    {
        $user = Auth::guard('api')->user();
        if (! $user instanceof User) {
            return false;
        }

        return $user->isAdmin();
    }
}
