<?php

namespace App\Http\Resources;

use App\Support\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body_markdown' => $this->body_markdown,
            'category' => $this->category ? ['slug' => $this->category->slug, 'name' => $this->category->name] : null,
            'tags' => $this->tags->map(fn ($tag): array => ['slug' => $tag->slug, 'name' => $tag->name])->all(),
            'author' => $this->author->name,
            'reading_minutes' => $this->reading_minutes,
            'cover' => ImageData::fromMedia($this->getFirstMedia('cover'), 'Cover image for '.$this->title),
            'published_at' => $this->published_at?->toIso8601ZuluString(),
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
        ];
    }
}
