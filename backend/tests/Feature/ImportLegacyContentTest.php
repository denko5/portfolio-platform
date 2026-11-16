<?php

use App\Enums\PublicationStatus;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\SocialLink;
use App\Models\Technology;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);
});

function runContentImport(): void
{
    test()->artisan('portfolio:import-legacy-content')->assertSuccessful();
}

it('validates the content data without writing anything', function () {
    $this->artisan('portfolio:import-legacy-content --dry-run')->assertSuccessful();

    expect(Service::count())->toBe(0)->and(Experience::count())->toBe(0);
});

it('imports services, skills, experience, education, certifications and social links', function () {
    runContentImport();

    expect(Service::count())->toBe(7)
        ->and(Service::where('slug', 'data-analytics')->firstOrFail()->items)->toHaveCount(5)
        ->and(SkillCategory::count())->toBe(5)
        ->and(Skill::count())->toBe(24)
        ->and(Experience::count())->toBe(3)
        ->and(Education::count())->toBe(2)
        ->and(Certification::count())->toBe(3)
        ->and(SocialLink::count())->toBe(4);
});

it('saves imported content as drafts so nothing goes live unreviewed', function () {
    runContentImport();

    expect(Service::published()->count())->toBe(0)
        ->and(Experience::published()->count())->toBe(0)
        ->and(Education::published()->count())->toBe(0)
        ->and(Certification::published()->count())->toBe(0)
        ->and(Service::where('publication_status', PublicationStatus::Draft->value)->count())->toBe(7);
});

it('imports settings but never the contact details, and keeps Overcend hidden', function () {
    runContentImport();
    $settings = SiteSetting::current();

    expect($settings->headline)->toBe('Software Developer | Data Analyst')
        ->and($settings->email)->toBe('')
        ->and($settings->phone)->toBeNull()
        ->and($settings->whatsapp_number)->toBeNull()
        ->and($settings->contact_notification_email)->toBe('')
        ->and($settings->overcend_visible)->toBeFalse()
        ->and($settings->overcend_body)->toContain('emerging technology venture')
        ->and($settings->tiktok_handle)->toBe('code.ke');
});

it('does not use the product name in the imported bio', function () {
    runContentImport();

    expect(strtolower(SiteSetting::current()->bio_markdown))->not->toContain('lexops');
});

it('never overwrites settings the owner has already filled in', function () {
    SiteSetting::current()->update(['owner_name' => 'Chosen Name', 'intro_text' => 'My own intro', 'email' => 'owner@example.com']);

    runContentImport();
    $settings = SiteSetting::current();

    expect($settings->owner_name)->toBe('Chosen Name')
        ->and($settings->intro_text)->toBe('My own intro')
        ->and($settings->email)->toBe('owner@example.com')
        ->and($settings->location)->toBe('Kilifi, Kenya');
});

it('is safe to run twice', function () {
    runContentImport();
    runContentImport();

    expect(Service::count())->toBe(7)
        ->and(Skill::count())->toBe(24)
        ->and(Experience::count())->toBe(3)
        ->and(Certification::count())->toBe(3)
        ->and(SocialLink::count())->toBe(4)
        ->and(SiteSetting::count())->toBe(1);
});

it('marks the marquee technologies as featured and avoids duplicate technologies after the project import', function () {
    $this->artisan('portfolio:import-legacy-projects')->assertSuccessful();
    runContentImport();

    $names = Technology::pluck('name')->map(fn ($n) => strtolower(trim($n)));

    expect(Technology::where('is_featured', true)->count())->toBe(8)
        ->and($names->count())->toBe($names->unique()->count())
        ->and(Technology::count())->toBe(43);
});

it('treats technology names with stray spaces or different case as the same technology', function () {
    Technology::create(['name' => 'Power BI ', 'slug' => 'BI', 'type' => 'tool']);

    $this->artisan('portfolio:import-legacy-projects')->assertSuccessful();

    expect(Technology::whereRaw('lower(trim(name)) = ?', ['power bi'])->count())->toBe(1);
});
