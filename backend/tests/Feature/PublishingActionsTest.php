<?php

use App\Enums\PublicationStatus;
use App\Filament\Support\PublishingActions;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('publishes projects but skips the ones with a publishing hold', function () {
    $category = ProjectCategory::create(['name' => 'Dashboards', 'slug' => 'dashboards']);
    $base = ['category_id' => $category->id, 'summary' => 'S'];
    Project::create($base + ['slug' => 'free', 'title' => 'Free project']);
    Project::create($base + ['slug' => 'held', 'title' => 'Held project', 'publish_block_reason' => 'Needs review']);

    $result = PublishingActions::setStatus(Project::all(), PublicationStatus::Published);

    expect($result)->toBe(['changed' => 1, 'blocked' => ['Held project']])
        ->and(Project::published()->pluck('slug')->all())->toBe(['free']);
});

it('moves published items back to draft', function () {
    $category = ProjectCategory::create(['name' => 'Software', 'slug' => 'software']);
    Project::create(['slug' => 'live', 'title' => 'Live', 'category_id' => $category->id, 'summary' => 'S', 'publication_status' => PublicationStatus::Published]);

    $result = PublishingActions::setStatus(Project::all(), PublicationStatus::Draft);

    expect($result['changed'])->toBe(1)
        ->and(Project::published()->count())->toBe(0);
});

it('publishes ordinary content such as services in one go', function () {
    foreach (['a', 'b', 'c'] as $slug) {
        Service::create(['slug' => $slug, 'title' => strtoupper($slug), 'summary' => 'S', 'icon_key' => 'code']);
    }

    $result = PublishingActions::setStatus(Service::all(), PublicationStatus::Published);

    expect($result)->toBe(['changed' => 3, 'blocked' => []])
        ->and(Service::published()->count())->toBe(3);
});
