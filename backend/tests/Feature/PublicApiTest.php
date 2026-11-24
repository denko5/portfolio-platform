<?php

use App\Models\Article;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\ProjectLink;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);
});

function importEverything(): void
{
    test()->artisan('portfolio:import-legacy-projects')->assertSuccessful();
    test()->artisan('portfolio:import-legacy-content')->assertSuccessful();
}

function publishAll(string $model): void
{
    // Query-builder update on purpose: it skips model events, so held projects publish too.
    $values = ['publication_status' => 'published'];
    if (Schema::hasColumn((new $model)->getTable(), 'published_at')) {
        $values['published_at'] = now();
    }

    $model::query()->update($values);
}

it('returns nothing public while all content is still a draft', function () {
    importEverything();

    foreach (['projects', 'services', 'experiences', 'education', 'certifications', 'articles'] as $endpoint) {
        $this->getJson('/api/v1/'.$endpoint)->assertOk()->assertJsonCount(0, 'data');
    }
});

it('lists only published projects and hides drafts, with pagination', function () {
    importEverything();
    Project::whereIn('slug', ['ecommerce-sales-dashboard', 'hotel-bi-dashboard'])
        ->update(['publication_status' => 'published', 'published_at' => now()]);

    $response = $this->getJson('/api/v1/projects')->assertOk();

    $response->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.0.slug', 'ecommerce-sales-dashboard')
        ->assertJsonPath('data.1.slug', 'hotel-bi-dashboard');
    expect($response->json('data.0.cover.alt'))->not->toBeEmpty()
        ->and($response->json('data.0.cover.is_placeholder'))->toBeFalse();
});

it('returns 404 with a plain message for a draft project and for unknown routes', function () {
    importEverything();

    $this->getJson('/api/v1/projects/nexahub')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
    $this->getJson('/api/v1/projects/does-not-exist')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
    $this->getJson('/api/v1/nope')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
});

it('shows full project detail with features, visible links, images and downloads', function () {
    importEverything();
    publishAll(Project::class);

    $project = Project::where('slug', 'tableau-hr-dashboard')->firstOrFail();
    $project->links()->create(['label' => 'Hidden link', 'url' => 'https://example.com/hidden', 'type' => 'other', 'is_visible' => false, 'sort_order' => 9]);

    $json = $this->getJson('/api/v1/projects/tableau-hr-dashboard')->assertOk()->json('data');

    expect($json['features'])->not->toBeEmpty()
        ->and(collect($json['links'])->pluck('label')->all())->not->toContain('Hidden link')
        ->and($json['images'])->toHaveCount(1)
        ->and($json['images'][0]['alt'])->not->toBeEmpty()
        ->and($json['images'][0]['original_url'])->toStartWith('/storage/')
        ->and($json['downloads'])->toHaveCount(1)
        ->and($json['downloads'][0]['url'])->toStartWith('/storage/')
        ->and($json)->toHaveKeys(['problem', 'objectives', 'role', 'approach', 'challenges', 'outcomes', 'lessons', 'seo']);
});

it('marks placeholder images so the site can label them', function () {
    importEverything();
    publishAll(Project::class);

    $json = $this->getJson('/api/v1/projects/nexahub')->assertOk()->json('data');

    expect($json['images'][0]['is_placeholder'])->toBeTrue();
});

it('filters projects by category, technology and featured flag', function () {
    importEverything();
    publishAll(Project::class);

    $dashboards = $this->getJson('/api/v1/projects?category=dashboards&per_page=50')->assertOk();
    expect($dashboards->json('meta.total'))->toBe(7);

    $laravel = $this->getJson('/api/v1/projects?technology=laravel&per_page=50')->assertOk()->json('data');
    expect($laravel)->not->toBeEmpty()
        ->and(collect($laravel)->every(fn ($p) => collect($p['technologies'])->pluck('slug')->contains('laravel')))->toBeTrue();

    $this->getJson('/api/v1/projects?featured=true')->assertOk()->assertJsonPath('meta.total', 3);
    $this->getJson('/api/v1/projects?featured=1')->assertOk()->assertJsonPath('meta.total', 3);
    $this->getJson('/api/v1/projects?featured=false&per_page=50')->assertOk()->assertJsonPath('meta.total', 17);
});

it('rejects invalid project query parameters with a 422', function () {
    $this->getJson('/api/v1/projects?per_page=500')->assertStatus(422)->assertJsonValidationErrors('per_page');
    $this->getJson('/api/v1/projects?sort=bogus')->assertStatus(422)->assertJsonValidationErrors('sort');
    $this->getJson('/api/v1/projects?featured=maybe')->assertStatus(422)->assertJsonValidationErrors('featured');
});

it('serves published services, experience, education and certifications only', function () {
    importEverything();
    foreach ([Service::class, Experience::class, Education::class, Certification::class] as $model) {
        publishAll($model);
    }
    Service::where('slug', 'data-analytics')->update(['publication_status' => 'draft']);

    $this->getJson('/api/v1/services')->assertOk()->assertJsonCount(6, 'data');
    $this->getJson('/api/v1/experiences')->assertOk()->assertJsonCount(3, 'data');
    $this->getJson('/api/v1/education')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/certifications')->assertOk()->assertJsonCount(3, 'data');
    expect($this->getJson('/api/v1/experiences')->json('data.0.highlights'))->not->toBeEmpty();
});

it('hides skills and social links that are switched off', function () {
    importEverything();
    Skill::where('name', 'Excel')->update(['is_visible' => false]);
    SocialLink::where('platform', 'x')->update(['is_visible' => false]);

    $skills = $this->getJson('/api/v1/skills')->assertOk()->json('data');
    $names = collect($skills)->flatMap(fn ($c) => collect($c['skills'])->pluck('name'));
    $platforms = collect($this->getJson('/api/v1/profile')->json('data.social_links'))->pluck('platform');

    expect($names)->not->toContain('Excel')->and($names)->toContain('Python')
        ->and($platforms)->not->toContain('x')->and($platforms)->toContain('github');
});

it('exposes the public profile but never the private notification email', function () {
    SiteSetting::current()->update([
        'email' => 'hello@example.com',
        'contact_notification_email' => 'private-inbox@example.com',
        'phone' => '+254 700 000 000',
        'whatsapp_number' => '254700000000',
        'overcend_visible' => false,
        'overcend_body' => 'An emerging venture.',
    ]);

    $response = $this->getJson('/api/v1/profile')->assertOk();

    $response->assertJsonPath('data.contact.email', 'hello@example.com')
        ->assertJsonPath('data.contact.whatsapp_number', '254700000000')
        ->assertJsonPath('data.overcend', null);
    expect($response->getContent())->not->toContain('private-inbox@example.com');
});

it('only exposes the Overcend block once the owner makes it visible', function () {
    SiteSetting::current()->update(['overcend_visible' => true, 'overcend_heading' => 'Overcend', 'overcend_body' => 'An emerging venture.']);

    $this->getJson('/api/v1/profile')->assertOk()->assertJsonPath('data.overcend.body', 'An emerging venture.');
});

it('lists technologies with their type and featured flag', function () {
    importEverything();

    $json = $this->getJson('/api/v1/technologies')->assertOk()->json('data');

    expect(collect($json)->firstWhere('slug', 'python'))->toMatchArray(['name' => 'Python', 'type' => 'language']);
});

it('serves only published, already-dated articles', function () {
    $author = User::factory()->create(['name' => 'Denis']);
    $base = ['excerpt' => 'E', 'body_markdown' => '# Body', 'author_id' => $author->id];
    Article::create($base + ['slug' => 'draft', 'title' => 'Draft']);
    Article::create($base + ['slug' => 'future', 'title' => 'Future', 'publication_status' => 'published', 'published_at' => now()->addDay()]);
    Article::create($base + ['slug' => 'live', 'title' => 'Live', 'publication_status' => 'published', 'published_at' => now()->subDay()]);

    $this->getJson('/api/v1/articles')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'live');
    $this->getJson('/api/v1/articles/live')->assertOk()->assertJsonPath('data.body_markdown', '# Body')->assertJsonPath('data.author', 'Denis');
    $this->getJson('/api/v1/articles/draft')->assertNotFound();
    $this->getJson('/api/v1/articles/future')->assertNotFound();
});

it('sends an ETag and answers 304 when nothing changed', function () {
    $first = $this->getJson('/api/v1/technologies')->assertOk();
    $etag = $first->headers->get('ETag');

    expect($etag)->not->toBeNull()
        ->and($first->headers->get('Cache-Control'))->toContain('max-age=60');

    $this->withHeaders(['If-None-Match' => $etag])->getJson('/api/v1/technologies')->assertStatus(304);
});

it('rate limits the public API to 120 requests per minute', function () {
    for ($i = 0; $i < 120; $i++) {
        $this->getJson('/api/v1/technologies')->assertOk();
    }

    $this->getJson('/api/v1/technologies')
        ->assertStatus(429)
        ->assertJson(['message' => 'Too many requests.'])
        ->assertHeader('Retry-After');
});

it('never lets browsers cache an error response', function () {
    importEverything();

    $notFound = $this->getJson('/api/v1/projects/nexahub')->assertNotFound();
    expect($notFound->headers->get('Cache-Control'))->toContain('no-store');

    // Once it is published the very next request must see it.
    Project::where('slug', 'nexahub')->update(['publication_status' => 'published', 'published_at' => now()]);
    $this->getJson('/api/v1/projects/nexahub')->assertOk();
});
