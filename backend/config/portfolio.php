<?php

return [

    /*
    | Email addresses allowed into the Filament admin panel (comma separated in ADMIN_EMAILS).
    | Leave empty to deny everyone. Never commit real values; set them in .env.
    */
    'admin_emails' => array_values(array_filter(array_map(
        fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('ADMIN_EMAILS', ''))
    ))),

    /*
    | Require administrators to set up app-based two-factor authentication (TOTP).
    | Secure default is true. Tests turn it off in phpunit.xml.
    */
    'require_admin_mfa' => (bool) env('ADMIN_REQUIRE_MFA', true),

    /*
    | Optional Cloudflare Turnstile spam check for the contact form. Off by default.
    | When enabled, the form must send a valid cf-turnstile-response token, and a failed
    | or unreachable check rejects the submission.
    */
    'turnstile' => [
        'enabled' => (bool) env('TURNSTILE_ENABLED', false),
        'secret' => env('TURNSTILE_SECRET'),
    ],

];
