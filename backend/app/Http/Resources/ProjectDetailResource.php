<?php

namespace App\Http\Resources;

use App\Support\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailResource extends JsonResource
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
            'problem' => $this->problem,
            'objectives' => $this->objectives,
            'role' => $this->role,
            'approach' => $this->approach,
            'challenges' => $this->challenges,
            'outcomes' => $this->outcomes,
            'lessons' => $this->lessons,
            'description_markdown' => $this->description_markdown,
            'public_note' => $this->public_note,
            'link_note' => $this->link_note,
            'features' => $this->features->pluck('body')->all(),
            'links' => $this->links->where('is_visible', true)->map(fn ($link): array => [
                'label' => $link->label,
                'url' => $link->url,
                'type' => $link->type->value,
            ])->values()->all(),
            'images' => $this->images->map(fn ($image): ?array => ImageData::fromProjectImage($image))->filter()->values()->all(),
            'downloads' => $this->getMedia('downloads')->map(fn ($media): array => ImageData::download($media))->values()->all(),
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
        ];
    }
}
