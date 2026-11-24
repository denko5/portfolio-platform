<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleDetailResource;
use App\Http\Resources\ArticleSummaryResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    /** Paginated published articles. Empty until the first article is published. */
    public function index(): AnonymousResourceCollection
    {
        $articles = Article::query()->published()
            ->with(['category', 'tags', 'author', 'media'])
            ->orderByDesc('published_at')
            ->paginate(12);

        return ArticleSummaryResource::collection($articles);
    }

    /** One published article. Drafts and scheduled articles return 404. */
    public function show(string $slug): ArticleDetailResource
    {
        $article = Article::query()->published()
            ->with(['category', 'tags', 'author', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ArticleDetailResource($article);
    }
}
