<?php

use App\Enums\PublicationStatus;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class, RefreshDatabase::class);

function guardProject(array $overrides = []): Project
{
    $category = ProjectCategory::firstOrCreate(['slug' => 'dashboards'], ['name' => 'Dashboards']);

    return Project::create(array_merge([
        'slug' => 'p-'.uniqid(),
        'title' => 'Guarded project',
        'category_id' => $category->id,
        'summary' => 'Summary.',
    ], $overrides));
}

it('blocks publishing while a publishing hold is set', function () {
    $project = guardProject(['publish_block_reason' => 'Screenshot shows outdated contact details']);

    expect(fn () => $project->update(['publication_status' => PublicationStatus::Published]))
        ->toThrow(ValidationException::class);

    expect($project->fresh()->publication_status)->toBe(PublicationStatus::Draft);
});

it('allows saving a held project as a draft', function () {
    $project = guardProject(['publish_block_reason' => 'Needs review']);

    $project->update(['title' => 'Renamed']);

    expect($project->fresh()->title)->toBe('Renamed');
});

it('publishes once the hold is cleared and sets the publish date', function () {
    $project = guardProject(['publish_block_reason' => 'Needs review']);

    $project->update(['publish_block_reason' => null, 'publication_status' => PublicationStatus::Published]);

    expect($project->fresh()->publication_status)->toBe(PublicationStatus::Published)
        ->and($project->fresh()->published_at)->not->toBeNull();
});

it('stores images with alt text and a placeholder flag and removes them with the project', function () {
    $project = guardProject();
    $project->images()->create(['alt' => 'Dashboard overview', 'is_placeholder' => true, 'sort_order' => 1]);
    $project->images()->create(['alt' => 'Second view', 'sort_order' => 2]);

    expect($project->images)->toHaveCount(2)
        ->and($project->images->first()->is_placeholder)->toBeTrue()
        ->and($project->images->last()->is_placeholder)->toBeFalse();

    $project->forceDelete();

    expect(ProjectImage::count())->toBe(0);
});

it('lets an allow-listed admin open the projects list and the create page', function () {
    config(['portfolio.admin_emails' => ['owner@example.com']]);
    $admin = User::factory()->create(['email' => 'owner@example.com']);

    $this->actingAs($admin)->get('/admin/projects')->assertOk();
    $this->actingAs($admin)->get('/admin/projects/create')->assertOk();
});

it('keeps non-allow-listed users out of the projects pages', function () {
    config(['portfolio.admin_emails' => ['owner@example.com']]);
    $user = User::factory()->create(['email' => 'someone@example.com']);

    $this->actingAs($user)->get('/admin/projects')->assertForbidden();
});
