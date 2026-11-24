<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'organisation' => $this->organisation,
            'role_title' => $this->role_title,
            'location' => $this->location,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_current' => (bool) $this->is_current,
            'summary' => $this->summary,
            'highlights' => $this->highlights->pluck('body')->all(),
            'technologies' => TechnologyResource::collection($this->technologies),
        ];
    }
}
