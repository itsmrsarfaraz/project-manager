<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'owner'       => new UserResource($this->whenLoaded('owner')),
            'members'     => UserResource::collection($this->whenLoaded('members')),
            'tasks'       => TaskResource::collection($this->whenLoaded('tasks')),
            'tasks_count'           => $this->whenNotNull($this->tasks_count ?? null),
            'completed_tasks_count' => $this->whenNotNull($this->completed_tasks_count ?? null),
            'my_role' => $this->when(
                $this->relationLoaded('members'),
                fn() => $this->members
                    ->find($request->user()?->id)
                    ?->pivot->role
            ),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
