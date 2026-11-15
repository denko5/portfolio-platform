<?php

use App\Enums\EnquiryStatus;
use App\Enums\PublicationStatus;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectFeature;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeCategory(string $slug = 'dashboards'): ProjectCategory
{
    return ProjectCategory::create(['name' => ucfirst($slug), 'slug' => $slug]);
}

function makeProject(array $overrides = []): Project
{
    $category = $overrides['category'] ?? makeCategory('cat-'.uniqid());
    unset($overrides['category']);

    return Project::create(array_merge([
        'slug' => 'project-'.uniqid(),
        'title' => 'A project',
        'category_id' => $category->id,
        'summary' => 'A summary.',
    ], $overrides));
}

it('creates every release 1 table', function () {
    $tables = [
        'site_settings', 'social_links', 'project_categories', 'technologies', 'projects',
        'project_features', 'project_links', 'project_images', 'project_technology', 'experiences',
        'experience_highlights', 'experience_technology', 'education', 'certifications',
        'skill_categories', 'skills', 'services', 'service_items', 'service_technology',
        'article_categories', 'tags', 'articles', 'article_tag', 'contact_messages', 'site_builds',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Missing table: {$table}");
    }
});

it('defaults new projects to draft and keeps them out of the published scope', function () {
    $draft = makeProject();
    $published = makeProject(['publication_status' => PublicationStatus::Published]);

    expect($draft->fresh()->publication_status)->toBe(PublicationStatus::Draft)
        ->and(Project::published()->pluck('id')->all())->toBe([$published->id]);
});

it('enforces unique project slugs', function () {
    $category = makeCategory();
    makeProject(['slug' => 'same-slug', 'category' => $category]);

    expect(fn () => makeProject(['slug' => 'same-slug', 'category' => $category]))
        ->toThrow(QueryException::class);
});

it('cascades project children when a project is permanently deleted', function () {
    $project = makeProject();
    $project->features()->create(['body' => 'A feature']);
    $project->technologies()->attach(Technology::create(['name' => 'Laravel', 'slug' => 'laravel'])->id);

    $project->forceDelete();

    expect(ProjectFeature::count())->toBe(0)
        ->and(DB::table('project_technology')->count())->toBe(0);
});

it('restricts deleting a category that still has projects', function () {
    $category = makeCategory();
    makeProject(['category' => $category]);

    expect(fn () => $category->delete())->toThrow(QueryException::class);
});

it('soft deletes projects so they can be restored', function () {
    $project = makeProject();
    $project->delete();

    expect(Project::count())->toBe(0)
        ->and(Project::withTrashed()->count())->toBe(1);
});

it('only treats articles as published when status is published and the date has passed', function () {
    $author = User::factory()->create();
    $base = ['title' => 'T', 'excerpt' => 'E', 'body_markdown' => 'Body', 'author_id' => $author->id];

    Article::create($base + ['slug' => 'draft']);
    Article::create($base + ['slug' => 'future', 'publication_status' => PublicationStatus::Published, 'published_at' => now()->addDay()]);
    $live = Article::create($base + ['slug' => 'live', 'publication_status' => PublicationStatus::Published, 'published_at' => now()->subDay()]);

    expect(Article::published()->pluck('id')->all())->toBe([$live->id]);
});

it('gives contact messages a unique reference and a New status', function () {
    $data = [
        'name' => 'Visitor', 'email' => 'visitor@example.com', 'enquiry_type' => 'general',
        'subject' => 'Hello', 'message' => 'A message long enough.', 'privacy_accepted_at' => now(),
    ];

    $a = ContactMessage::create($data);
    $b = ContactMessage::create($data);

    expect($a->reference)->toHaveLength(26)
        ->and($a->reference)->not->toBe($b->reference)
        ->and($a->fresh()->status)->toBe(EnquiryStatus::New);
});
