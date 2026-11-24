<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectIndexRequest;
use App\Http\Resources\ProjectDetailResource;
use App\Http\Resources\ProjectSummaryResource;
use App\Models\Project;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /** Paginated list of published projects. */
    public function index(ProjectIndexRequest $request): AnonymousResourceCollection
    {
        $query = Project::query()->published()->with(['category', 'technologies', 'images.media']);

        if ($category = $request->validated('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }
        if ($technology = $request->validated('technology')) {
            $query->whereHas('technologies', fn ($q) => $q->where('slug', $technology));
        }
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        match ($request->validated('sort', 'sort_order')) {
            '-published_at' => $query->orderByDesc('published_at')->orderBy('id'),
            'title' => $query->orderBy('title')->orderBy('id'),
            default => $query->orderBy('sort_order')->orderBy('id'),
        };

        $projects = $query->paginate($request->integer('per_page', 12))->withQueryString();

        return ProjectSummaryResource::collection($projects);
    }

    /** One published project. Drafts and unknown slugs return 404. */
    public function show(string $slug): ProjectDetailResource
    {
        $project = Project::query()->published()
            ->with(['category', 'technologies', 'features', 'links', 'images.media', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProjectDetailResource($project);
    }
}
