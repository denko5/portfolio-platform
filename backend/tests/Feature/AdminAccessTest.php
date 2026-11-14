<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('sends guests to the admin login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('allows only allow-listed emails into the admin panel', function () {
    config(['portfolio.admin_emails' => ['owner@example.com']]);
    $panel = Filament::getPanel('admin');

    $owner = User::factory()->create(['email' => 'Owner@Example.com']);
    $other = User::factory()->create(['email' => 'someone@example.com']);

    expect($owner->canAccessPanel($panel))->toBeTrue()
        ->and($other->canAccessPanel($panel))->toBeFalse();
});

it('denies everyone when the allow-list is empty', function () {
    config(['portfolio.admin_emails' => []]);

    $user = User::factory()->create();

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('returns 403 for a signed-in user who is not allow-listed', function () {
    config(['portfolio.admin_emails' => ['owner@example.com']]);
    $user = User::factory()->create(['email' => 'someone@example.com']);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('lets an allow-listed admin open the dashboard', function () {
    config(['portfolio.admin_emails' => ['owner@example.com']]);
    $admin = User::factory()->create(['email' => 'owner@example.com']);

    $this->actingAs($admin)->get('/admin')->assertOk();
});
