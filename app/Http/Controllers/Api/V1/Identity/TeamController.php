<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\StoreTeamRequest;
use App\Http\Requests\Identity\UpdateTeamRequest;
use App\Http\Resources\Identity\TeamResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TeamResource::collection(Team::with('department')->paginate());
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

    public function destroy(Team $team): JsonResponse
    {
        if (! Auth::guard('api')->user()?->isAdmin()) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $team->delete();

        return response()->json(['message' => 'Team soft-deleted successfully']);
    }
}
