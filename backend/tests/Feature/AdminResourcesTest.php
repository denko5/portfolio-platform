<?php

use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\SkillCategory;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public', 'portfolio.admin_emails' => ['owner@example.com']]);
    $this->admin = User::factory()->create(['email' => 'owner@example.com']);
});

it('opens the list page of every admin section', function (string $slug) {
    $this->actingAs($this->admin)->get('/admin/'.$slug)->assertOk();
})->with([
    'projects', 'project-categories', 'technologies', 'services', 'skill-categories',
    'experiences', 'education', 'certifications', 'social-links', 'site-settings',
]);

it('opens the create page of every admin section that allows creating', function (string $slug) {
    $this->actingAs($this->admin)->get('/admin/'.$slug.'/create')->assertOk();
})->with([
    'projects', 'project-categories', 'technologies', 'services', 'skill-categories',
    'experiences', 'education', 'certifications', 'social-links',
]);

it('opens the edit page of every imported record', function () {
    $this->artisan('portfolio:import-legacy-projects')->assertSuccessful();
    $this->artisan('portfolio:import-legacy-content')->assertSuccessful();

    $pages = [
        'projects' => Project::first(),
        'project-categories' => ProjectCategory::first(),
        'technologies' => Technology::first(),
        'services' => Service::first(),
        'skill-categories' => SkillCategory::first(),
        'experiences' => Experience::first(),
        'education' => Education::first(),
        'certifications' => Certification::first(),
        'social-links' => SocialLink::first(),
        'site-settings' => SiteSetting::current(),
    ];

    foreach ($pages as $slug => $record) {
        expect($record)->not->toBeNull("No record for {$slug}");
        $this->actingAs($this->admin)->get("/admin/{$slug}/{$record->getKey()}/edit")->assertOk();
    }
});

it('does not let anyone create a second settings record from the admin', function () {
    $this->actingAs($this->admin)->get('/admin/site-settings/create')->assertNotFound();
});

it('keeps non-allow-listed users out of every new admin section', function () {
    $user = User::factory()->create(['email' => 'someone@example.com']);

    foreach (['services', 'skill-categories', 'experiences', 'education', 'certifications', 'social-links', 'site-settings'] as $slug) {
        $this->actingAs($user)->get('/admin/'.$slug)->assertForbidden();
    }
});
