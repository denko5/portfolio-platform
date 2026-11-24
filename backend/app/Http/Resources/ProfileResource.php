<?php

namespace App\Http\Resources;

use App\Models\SocialLink;
use App\Support\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->owner_name,
            'headline' => $this->headline,
            'intro' => $this->intro_text,
            'bio_markdown' => $this->bio_markdown,
            'location' => $this->location,
            'contact' => [
                'email' => filled($this->email) ? $this->email : null,
                'phone' => $this->phone,
                'whatsapp_number' => $this->whatsapp_number,
            ],
            'tiktok' => [
                'handle' => $this->tiktok_handle,
                'followers_label' => $this->tiktok_followers_label,
            ],
            'social_links' => SocialLink::query()->visible()->orderBy('sort_order')->get()
                ->map(fn (SocialLink $link): array => ['platform' => $link->platform, 'label' => $link->label, 'url' => $link->url])->all(),
            // The Overcend block is only exposed when the owner has chosen to show it.
            'overcend' => $this->overcend_visible
                ? ['heading' => $this->overcend_heading, 'body' => $this->overcend_body]
                : null,
            'seo' => [
                'title' => $this->default_seo_title,
                'description' => $this->default_seo_description,
            ],
            'portrait' => ImageData::fromMedia($this->getFirstMedia('portrait'), $this->owner_name),
            'cv' => ($cv = $this->getFirstMedia('cv')) ? ImageData::download($cv) : null,
            'default_og_image' => ImageData::fromMedia($this->getFirstMedia('default-og-image'), $this->owner_name),
            'content_showcase' => $this->getMedia('content-showcase')
                ->map(fn ($media): ?array => ImageData::fromMedia($media, 'Video cover from '.$this->tiktok_handle))->values()->all(),
        ];
    }
}
