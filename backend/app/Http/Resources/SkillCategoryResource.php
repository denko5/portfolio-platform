<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillCategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'skills' => $this->skills->map(fn ($skill): array => [
                'name' => $skill->name,
                'description' => $skill->description,
            ])->all(),
        ];
    }
}
