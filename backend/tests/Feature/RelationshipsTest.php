<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Experience;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Tag;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function relProject(?ProjectCategory $category = null): Project
{
    $category ??= ProjectCategory::create(['name' => 'Software', 'slug' => 'software']);

    return Project::create(['slug' => 'p-'.uniqid(), 'title' => 'P', 'category_id' => $category->id, 'summary' => 'S']);
}

it('loads projects through a project category and the category through a project', function () {
    $category = ProjectCategory::create(['name' => 'Dashboards', 'slug' => 'dashboards']);
    $project = relProject($category);

    expect($category->projects)->toHaveCount(1)
        ->and($category->projects->first()->is($project))->toBeTrue()
        ->and($project->category->is($category))->toBeTrue();
});

it('loads skills through a skill category in sort order', function () {
    $category = SkillCategory::create(['name' => 'Data', 'slug' => 'data']);
    Skill::create(['category_id' => $category->id, 'name' => 'Second', 'sort_order' => 20]);
    Skill::create(['category_id' => $category->id, 'name' => 'First', 'sort_order' => 10]);

    expect($category->skills->pluck('name')->all())->toBe(['First', 'Second'])
        ->and($category->skills->first()->category->is($category))->toBeTrue();
});

it('loads articles through an article category', function () {
    $category = ArticleCategory::create(['name' => 'Notes', 'slug' => 'notes']);
    $author = User::factory()->create();
    $article = Article::create([
        'slug' => 'a', 'title' => 'A', 'excerpt' => 'E', 'body_markdown' => 'B',
        'category_id' => $category->id, 'author_id' => $author->id,
    ]);

    expect($category->articles)->toHaveCount(1)
        ->and($article->category->is($category))->toBeTrue()
        ->and($article->author->is($author))->toBeTrue();
});

it('loads project features, links and images in order and technologies by pivot order', function () {
    $project = relProject();
    $project->features()->create(['body' => 'Second', 'sort_order' => 2]);
    $project->features()->create(['body' => 'First', 'sort_order' => 1]);
    $project->links()->create(['label' => 'Repo', 'url' => 'https://example.com', 'type' => 'github', 'sort_order' => 1]);
    $project->images()->create(['alt' => 'Alt', 'sort_order' => 1]);

    $alpha = Technology::create(['name' => 'Alpha', 'slug' => 'alpha']);
    $beta = Technology::create(['name' => 'Beta', 'slug' => 'beta']);
    $project->technologies()->attach($alpha->id, ['sort_order' => 2]);
    $project->technologies()->attach($beta->id, ['sort_order' => 1]);

    expect($project->features->pluck('body')->all())->toBe(['First', 'Second'])
        ->and($project->links)->toHaveCount(1)
        ->and($project->images)->toHaveCount(1)
        ->and($project->technologies->pluck('name')->all())->toBe(['Beta', 'Alpha'])
        ->and($alpha->projects->first()->is($project))->toBeTrue();
});

it('loads service items and technologies', function () {
    $service = Service::create(['slug' => 's', 'title' => 'S', 'summary' => 'Sum', 'icon_key' => 'code']);
    $service->items()->create(['body' => 'Item', 'sort_order' => 1]);
    $service->technologies()->attach(Technology::create(['name' => 'PHP', 'slug' => 'php'])->id);

    expect($service->items)->toHaveCount(1)->and($service->technologies)->toHaveCount(1);
});

it('loads experience highlights and technologies', function () {
    $experience = Experience::create(['organisation' => 'Org', 'role_title' => 'Role', 'start_date' => '2025-01-01']);
    $experience->highlights()->create(['body' => 'Did a thing', 'sort_order' => 1]);
    $experience->technologies()->attach(Technology::create(['name' => 'Excel', 'slug' => 'excel'])->id);

    expect($experience->highlights)->toHaveCount(1)->and($experience->technologies)->toHaveCount(1);
});

it('loads article tags from both sides', function () {
    $author = User::factory()->create();
    $article = Article::create(['slug' => 'a2', 'title' => 'A', 'excerpt' => 'E', 'body_markdown' => 'B', 'author_id' => $author->id]);
    $tag = Tag::create(['name' => 'PHP', 'slug' => 'php']);
    $article->tags()->attach($tag->id);

    expect($article->tags)->toHaveCount(1)->and($tag->articles->first()->is($article))->toBeTrue();
});

it('lets a technology be used by every kind of content', function () {
    $tech = Technology::create(['name' => 'Laravel', 'slug' => 'laravel']);
    relProject()->technologies()->attach($tech->id);

    expect($tech->projects)->toHaveCount(1);
});
