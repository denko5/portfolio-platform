<?php

namespace App\Http\Resources;

use App\Support\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'category' => ['slug' => $this->category->slug, 'name' => $this->category->name],
            'status_label' => $this->status_label,
            'is_featured' => (bool) $this->is_featured,
            'technologies' => TechnologyResource::collection($this->technologies),
            'cover' => $this->images->isNotEmpty() ? ImageData::fromProjectImage($this->images->first()) : null,
            'published_at' => $this->published_at?->toIso8601ZuluString(),
        ];
    }
}
