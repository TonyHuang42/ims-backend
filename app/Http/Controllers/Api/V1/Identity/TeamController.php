<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreTeamRequest;
use App\Http\Requests\Identity\UpdateTeamRequest;
use App\Http\Resources\Identity\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Team::query()->with('department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        return TeamResource::collection($query->paginate($perPage));
    }

    public function store(StoreTeamRequest $request): TeamResource
    {
        $team = Team::create($request->validated());

        return new TeamResource($team->load('department'));
    }

    public function show(Team $team): TeamResource
    {
        return new TeamResource($team->load('department'));
    }

    public function update(UpdateTeamRequest $request, Team $team): TeamResource
    {
        $team->update($request->validated());

        return new TeamResource($team->load('department'));
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
