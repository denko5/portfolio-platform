<?php

use App\Enums\PublicationStatus;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);
});

function runImport(): void
{
    test()->artisan('portfolio:import-legacy-projects')->assertSuccessful();
}

it('validates the legacy data without writing anything', function () {
    $this->artisan('portfolio:import-legacy-projects --dry-run')->assertSuccessful();

    expect(Project::count())->toBe(0);
});

it('imports all 20 legacy projects as drafts', function () {
    runImport();

    expect(Project::count())->toBe(20)
        ->and(Project::where('publication_status', PublicationStatus::Draft->value)->count())->toBe(20)
        ->and(Project::published()->count())->toBe(0);
});

it('is safe to run twice', function () {
    runImport();
    runImport();

    expect(Project::count())->toBe(20)
        ->and(ProjectImage::count())->toBe(22);
});

it('puts publishing holds on the projects that need review', function () {
    runImport();

    foreach ([
        'legal-operations-case-management-platform', 'social-media-dashboard',
        'lung-cancer-dashboard', 'tableau-hr-dashboard', 'kenya-hr-hub',
    ] as $slug) {
        expect(Project::where('slug', $slug)->firstOrFail()->publish_block_reason)->not->toBeEmpty();
    }

    expect(Project::whereNotNull('publish_block_reason')->count())->toBe(5);
});

it('uses the neutral legal operations name and never the product name in public fields', function () {
    runImport();

    $project = Project::where('slug', 'legal-operations-case-management-platform')->firstOrFail();
    $text = strtolower(implode(' ', [
        $project->title, $project->slug, $project->summary, (string) $project->problem, (string) $project->public_note,
        ...$project->images->pluck('alt')->all(),
        ...$project->features->pluck('body')->all(),
    ]));

    expect($project->title)->toBe('Legal Operations and Case Management Platform')
        ->and($text)->not->toContain('lexops');
});

it('imports every image with alt text, a stored file and the right placeholder flag', function () {
    runImport();

    expect(ProjectImage::count())->toBe(22)
        ->and(ProjectImage::where('is_placeholder', true)->count())->toBe(11)
        ->and(ProjectImage::where('is_placeholder', false)->count())->toBe(11);

    foreach (ProjectImage::all() as $image) {
        expect(trim($image->alt))->not->toBe('')
            ->and($image->getFirstMedia('image'))->not->toBeNull();
    }
});

it('does not import the screenshot with outdated contact details', function () {
    runImport();

    $project = Project::where('slug', 'social-media-dashboard')->firstOrFail();

    expect($project->images)->toHaveCount(0)
        ->and($project->publish_block_reason)->not->toBeEmpty();
});

it('does not import Power BI or dataset files, only the report PDF', function () {
    runImport();

    $downloads = Media::where('collection_name', 'downloads')->get();

    expect($downloads)->toHaveCount(1)
        ->and($downloads->first()->file_name)->toEndWith('.pdf');
});

it('classifies technologies and keeps the priority projects featured', function () {
    runImport();

    expect(Technology::count())->toBe(34)
        ->and(Technology::where('slug', 'python')->firstOrFail()->type->value)->toBe('language')
        ->and(Technology::where('slug', 'eda')->firstOrFail()->type->value)->toBe('concept')
        ->and(Project::where('is_featured', true)->pluck('slug')->sort()->values()->all())->toBe([
            'ecommerce-sales-dashboard', 'hotel-bi-dashboard', 'legal-operations-case-management-platform',
        ]);
});

it('reuses technologies and categories that already exist, whatever their slug or letter case', function () {
    Technology::create(['name' => 'Laravel', 'slug' => 'laravel-framework', 'type' => 'tool']);
    Technology::create(['name' => 'power bi', 'slug' => 'pbi', 'type' => 'tool']);
    ProjectCategory::create(['name' => 'dashboards', 'slug' => 'my-dashboards']);

    runImport();

    expect(Technology::count())->toBe(34)
        ->and(Technology::whereRaw('lower(name) = ?', ['laravel'])->count())->toBe(1)
        ->and(ProjectCategory::whereRaw('lower(name) = ?', ['dashboards'])->count())->toBe(1)
        ->and(Project::count())->toBe(20);
});
