<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkillCategoryResource;
use App\Models\SkillCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    /** Skill categories with their visible skills. Categories with no visible skills are left out. */
    public function __invoke(): AnonymousResourceCollection
    {
        $categories = SkillCategory::query()
            ->with(['skills' => fn ($query) => $query->where('is_visible', true)])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (SkillCategory $category): bool => $category->skills->isNotEmpty())
            ->values();

        return SkillCategoryResource::collection($categories);
    }
}
