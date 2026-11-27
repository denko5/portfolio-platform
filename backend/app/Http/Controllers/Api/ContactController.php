<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Notifications\NewContactMessage;
use App\Support\TurnstileVerifier;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactController extends Controller
{
    /** Accepted submissions allowed per hour. Invalid or duplicate attempts do not count. */
    private const MAX_PER_IP = 5;

    private const MAX_PER_EMAIL = 3;

    private const WINDOW_SECONDS = 3600;

    /** Receive an enquiry from the website contact form. */
    public function __invoke(StoreContactMessageRequest $request, TurnstileVerifier $turnstile): JsonResponse
    {
        // Honeypot: pretend it worked so bots learn nothing, but store and send nothing.
        if (filled($request->input('website'))) {
            return $this->received((string) Str::ulid());
        }

        if ($turnstile->enabled() && ! $turnstile->passes($request->input('cf-turnstile-response'), $request->ip())) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please complete the spam check and try again.',
            ]);
        }

        $data = $request->validated();

        // The same person sending the same message again within a day is not stored twice.
        $duplicate = ContactMessage::query()
            ->where('email', $data['email'])
            ->where('message', $data['message'])
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if ($duplicate) {
            return $this->received($duplicate->reference);
        }

        $limitKeys = $this->guardAgainstFlooding($request, $data['email']);

        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'organisation' => $data['organisation'] ?? null,
            'enquiry_type' => $data['enquiry_type'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'budget_range' => $data['budget_range'] ?? null,
            'privacy_accepted_at' => now(),
            // A keyed hash supports abuse limits without storing the raw IP address.
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        foreach ($limitKeys as $key) {
            RateLimiter::hit($key, self::WINDOW_SECONDS);
        }

        $this->notifyOwner($message);

        return $this->received($message->reference);
    }

    /**
     * Refuse the submission if this IP address or email address has already had its allowance
     * of accepted enquiries this hour. Returns the keys to count once the enquiry is stored.
     *
     * @return list<string>
     */
    private function guardAgainstFlooding(Request $request, string $email): array
    {
        $limits = [
            'contact-ip:'.$request->ip() => self::MAX_PER_IP,
            'contact-email:'.$email => self::MAX_PER_EMAIL,
        ];

        foreach ($limits as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw new ThrottleRequestsException('Too many requests.', null, ['Retry-After' => RateLimiter::availableIn($key)]);
            }
        }

        return array_keys($limits);
    }

    /** The enquiry is already saved. A mail problem must never lose it or fail the visitor. */
    private function notifyOwner(ContactMessage $message): void
    {
        $to = SiteSetting::current()->contact_notification_email;

        if (blank($to)) {
            Log::warning('Enquiry stored but no notification email is configured.', ['reference' => $message->reference]);

            return;
        }

        try {
            Notification::route('mail', $to)->notify(new NewContactMessage($message));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function received(string $reference): JsonResponse
    {
        return response()->json([
            'message' => 'Thanks, your message was received.',
            'data' => ['reference' => $reference],
        ], 201);
    }
}
