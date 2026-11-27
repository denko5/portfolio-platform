<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/** Optional Cloudflare Turnstile check. Fails closed: an unreachable service rejects the submission. */
class TurnstileVerifier
{
    public function enabled(): bool
    {
        return (bool) config('portfolio.turnstile.enabled');
    }

    public function passes(?string $token, ?string $ip): bool
    {
        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('portfolio.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return (bool) $response->json('success');
        } catch (Throwable) {
            return false;
        }
    }
}
