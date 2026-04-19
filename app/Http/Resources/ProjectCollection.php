<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            // add collection-level metadata here
            // e.g. 'total_active' => $this->collection->where('status', 'active')->count()
        ];
    }
}
