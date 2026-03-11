<?php

namespace App\Http\Resources\Identity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'role' => $this->whenLoaded('role', fn () => new RoleResource($this->role)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
