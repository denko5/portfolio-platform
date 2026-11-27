<?php

use App\Enums\EnquiryStatus;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    SiteSetting::current()->update(['contact_notification_email' => 'owner@example.com']);
});

function enquiry(array $overrides = []): array
{
    return array_merge([
        'name' => 'Amina Visitor',
        'email' => 'amina@example.com',
        'organisation' => 'Example Ltd',
        'enquiry_type' => 'software_development',
        'subject' => 'A dashboard for our clinic',
        'message' => 'We would like help building a reporting dashboard. '.uniqid(),
        'budget_range' => 'medium',
        'privacy_accepted' => true,
    ], $overrides);
}

it('stores a valid enquiry, returns a reference and notifies the owner', function () {
    $response = $this->postJson('/api/v1/contact', enquiry())
        ->assertCreated()
        ->assertJsonPath('message', 'Thanks, your message was received.');

    $stored = ContactMessage::firstOrFail();

    expect($response->json('data.reference'))->toBe($stored->reference)
        ->and($stored->status)->toBe(EnquiryStatus::New)
        ->and($stored->privacy_accepted_at)->not->toBeNull()
        ->and($stored->email)->toBe('amina@example.com');

    Notification::assertSentOnDemand(
        NewContactMessage::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'owner@example.com',
    );
});

it('stores a keyed hash of the IP address, never the raw address', function () {
    $this->postJson('/api/v1/contact', enquiry())->assertCreated();

    $hash = ContactMessage::firstOrFail()->ip_hash;

    expect($hash)->toHaveLength(64)->and($hash)->not->toContain('127.0.0.1');
});

it('normalises the email address to lower case', function () {
    $this->postJson('/api/v1/contact', enquiry(['email' => '  Amina@Example.COM ']))->assertCreated();

    expect(ContactMessage::firstOrFail()->email)->toBe('amina@example.com');
});

it('rejects invalid submissions with field errors', function () {
    $this->postJson('/api/v1/contact', [])->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'enquiry_type', 'subject', 'message', 'privacy_accepted']);

    $this->postJson('/api/v1/contact', enquiry(['email' => 'not-an-email']))->assertJsonValidationErrors('email');
    $this->postJson('/api/v1/contact', enquiry(['enquiry_type' => 'bogus']))->assertJsonValidationErrors('enquiry_type');
    $this->postJson('/api/v1/contact', enquiry(['message' => 'too short']))->assertJsonValidationErrors('message');
    $this->postJson('/api/v1/contact', enquiry(['message' => str_repeat('a', 5001)]))->assertJsonValidationErrors('message');
    $this->postJson('/api/v1/contact', enquiry(['budget_range' => 'millions']))->assertJsonValidationErrors('budget_range');
    $this->postJson('/api/v1/contact', enquiry(['privacy_accepted' => false]))->assertJsonValidationErrors('privacy_accepted');
    $this->postJson('/api/v1/contact', enquiry(['subject' => "Hello\r\nBcc: someone@example.com"]))->assertJsonValidationErrors('subject');

    expect(ContactMessage::count())->toBe(0);
});

it('looks successful to bots that fill the honeypot but stores and sends nothing', function () {
    $response = $this->postJson('/api/v1/contact', enquiry(['website' => 'http://spam.example']))->assertCreated();

    expect($response->json('data.reference'))->toHaveLength(26)
        ->and(ContactMessage::count())->toBe(0);
    Notification::assertNothingSent();
});

it('does not store the same message twice within a day', function () {
    $payload = enquiry(['message' => 'Exactly the same message sent twice by mistake.']);

    $first = $this->postJson('/api/v1/contact', $payload)->assertCreated()->json('data.reference');
    $second = $this->postJson('/api/v1/contact', $payload)->assertCreated()->json('data.reference');

    expect($second)->toBe($first)->and(ContactMessage::count())->toBe(1);
    Notification::assertSentOnDemandTimes(NewContactMessage::class, 1);
});

it('limits submissions to 5 per hour per IP address', function () {
    for ($i = 1; $i <= 5; $i++) {
        $this->postJson('/api/v1/contact', enquiry(['email' => "person{$i}@example.com"]))->assertCreated();
    }

    $this->postJson('/api/v1/contact', enquiry(['email' => 'person6@example.com']))
        ->assertStatus(429)
        ->assertJson(['message' => 'Too many requests.']);
});

it('limits submissions to 3 per hour per email address', function () {
    for ($i = 1; $i <= 3; $i++) {
        $this->postJson('/api/v1/contact', enquiry())->assertCreated();
    }

    $this->postJson('/api/v1/contact', enquiry())->assertStatus(429);
});

it('keeps the enquiry and still answers 201 when no notification email is set', function () {
    SiteSetting::current()->update(['contact_notification_email' => '']);

    $this->postJson('/api/v1/contact', enquiry())->assertCreated();

    expect(ContactMessage::count())->toBe(1);
    Notification::assertNothingSent();
});

it('can require a Turnstile check and rejects failed or missing tokens', function () {
    config(['portfolio.turnstile.enabled' => true, 'portfolio.turnstile.secret' => 'test-secret']);

    $this->postJson('/api/v1/contact', enquiry())->assertStatus(422)->assertJsonValidationErrors('cf-turnstile-response');

    // One fake with a sequence: Http::fake() keeps the first stub it is given.
    Http::fake(['challenges.cloudflare.com/*' => Http::sequence()
        ->push(['success' => false])
        ->push(['success' => true])]);

    $this->postJson('/api/v1/contact', enquiry(['cf-turnstile-response' => 'bad-token']))->assertStatus(422);
    $this->postJson('/api/v1/contact', enquiry(['cf-turnstile-response' => 'good-token']))->assertCreated();

    expect(ContactMessage::count())->toBe(1);
});

it('does not ask for Turnstile when it is switched off', function () {
    config(['portfolio.turnstile.enabled' => false]);

    $this->postJson('/api/v1/contact', enquiry())->assertCreated();
});

it('gives the owner an enquiries inbox that nobody else can open', function () {
    config(['portfolio.admin_emails' => ['owner@example.com']]);
    $admin = User::factory()->create(['email' => 'owner@example.com']);
    $other = User::factory()->create(['email' => 'someone@example.com']);
    $this->postJson('/api/v1/contact', enquiry())->assertCreated();
    $message = ContactMessage::firstOrFail();

    $this->actingAs($admin)->get('/admin/enquiries')->assertOk();
    $this->actingAs($admin)->get("/admin/enquiries/{$message->getKey()}/edit")->assertOk();
    $this->actingAs($admin)->get('/admin/enquiries/create')->assertNotFound();
    $this->actingAs($other)->get('/admin/enquiries')->assertForbidden();
});

it('does not expose enquiries through any public GET endpoint', function () {
    $this->postJson('/api/v1/contact', enquiry())->assertCreated();

    $this->getJson('/api/v1/contact')->assertStatus(405);
    $this->getJson('/api/v1/enquiries')->assertNotFound();
});

it('does not count invalid submissions against the visitor, so mistakes never lock them out', function () {
    for ($i = 0; $i < 8; $i++) {
        $this->postJson('/api/v1/contact', enquiry(['message' => 'short']))->assertStatus(422);
    }

    $this->postJson('/api/v1/contact', enquiry())->assertCreated();
});

it('does not count duplicate resubmissions against the visitor', function () {
    $payload = enquiry(['message' => 'The same message sent more than once by mistake.']);

    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/v1/contact', $payload)->assertCreated();
    }

    expect(ContactMessage::count())->toBe(1);
});

it('has a generous flood guard on raw attempts per IP address', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->postJson('/api/v1/contact', [])->assertStatus(422);
    }

    $this->postJson('/api/v1/contact', enquiry())->assertStatus(429);
});
