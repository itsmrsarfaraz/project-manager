<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'due_date'    => $this->due_date?->toDateString(), // "2025-12-31"
            'project_id'  => $this->project_id,

            // Conditional: only include if the relationship is loaded
            // Prevents N+1 by not forcing a query when not needed
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'labels'   => $this->whenLoaded(
                'labels',
                fn() =>
                $this->labels->map(fn($l) => [
                    'id'    => $l->id,
                    'name'  => $l->name,
                    'color' => $l->color,
                ])
            ),

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
