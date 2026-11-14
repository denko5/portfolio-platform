# Security

Status: **Draft for approval** (Stage 2). Reference framework: OWASP ASVS. Security is built in from the first stage, not added at the end. "Planned" means not yet implemented.

## 1. Threat summary

| Asset | Main threats | Key controls |
|---|---|---|
| Admin account | Credential stuffing, phishing, session theft | Strong password hashing, 2FA, login throttling, secure cookies |
| Contact data (personal data) | Leak, abuse, spam | Validation, least logging, access only for the admin, retention policy |
| Public site integrity | Defacement via compromised admin or build | 2FA, atomic releases with rollback, backups |
| Uploads | Malware, XSS via SVG, oversized files | MIME verification, size limits, random names, no SVG uploads by admin |
| Server | Brute force, misconfiguration | Key-only SSH, firewall, non-root deploy user, patching |
| Supply chain | Malicious dependency | Lockfiles, `composer audit`, `npm audit`, Dependabot, minimal dependencies |

## 2. Controls checklist

| Control | Plan | Stage |
|---|---|---|
| Password hashing (Laravel default bcrypt or argon) | Planned | 4 |
| Admin two-factor authentication | Written: Filament 5 app authentication (TOTP) with recovery codes, required by default; browser verification pending | 4 |
| Login throttling | Planned | 4 |
| Secure session and cookies (`Secure`, `HttpOnly`, `SameSite`) | Planned | 4 |
| Admin panel access allow-list (`ADMIN_EMAILS`, default deny) | Written, covered by Pest tests | 4 |
| Server-side authorization on every admin action | Planned | 4 |
| CSRF protection for admin | Framework default | 4 |
| Strict request validation (Form Requests) | Planned | 5 |
| API rate limiting | Planned | 5 |
| Output encoding and Markdown sanitization | Planned | 6 |
| Secure file uploads | See section 4 | 4 |
| Restricted CORS | Same-origin; dev origin only | 5 |
| Secure headers and CSP | See section 3 | 6 and 8 |
| HTTPS and HSTS (HSTS only after HTTPS is verified) | Planned | 8 |
| Secrets in environment only | Planned | 4 |
| No sensitive data in logs | Planned | 4 |
| Dependency vulnerability checks in CI | Planned | 4 |
| Admin action audit logging | **Deferred** (basic auth events logged now) | later |
| Production errors hidden | `APP_DEBUG=false` | 8 |

## 3. Content Security Policy (two separate policies)

The public site and the admin have different needs, so they get different policies set in Nginx.

### Public site (strict)

- `default-src 'self'`
- `script-src 'self'` (no inline scripts; JSON-LD data blocks are not executed)
- `style-src 'self'` (Astro configured to emit external CSS)
- `img-src 'self' data:`
- `font-src 'self'` (fonts are **self-hosted**, which also avoids a third-party request)
- `connect-src 'self'`
- `frame-ancestors 'none'`, `base-uri 'self'`, `form-action 'self'`
- If Turnstile is enabled: allow Cloudflare's challenge script and frame for the contact page only.

### Admin (`/admin`, Livewire, Filament)

Livewire and Alpine usually need a more permissive script policy than the public site. Start with `Content-Security-Policy-Report-Only`, review violations, then enforce the tightest policy that works. Whether Filament 5 and Livewire 4 support a CSP-friendly Alpine build is **to be verified** in Stage 4. The admin policy never applies to the public pages.

### Other headers

`X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` with unused features disabled, `X-Frame-Options: DENY` (alongside `frame-ancestors`).

## 4. File uploads

- Allow-list of MIME types verified from file content, not only the extension.
- Admin uploads: JPEG, PNG, WebP, AVIF, PDF. **SVG upload disabled** for admins (SVG can carry scripts). The legacy SVG placeholders are seeded by code, not uploaded.
- Maximum size per type (for example 5 MB images, 10 MB PDFs), validated server side.
- Randomized stored filenames; original names kept only as metadata.
- Public media on the public disk; anything private on a non-public disk, never in a web-served directory.
- Images get required alt text before a project can be published.

## 5. Privacy and Kenyan data protection

- The site stores personal data (enquiries). A **privacy policy page** is part of release 1 and the contact form requires acknowledgement.
- The Data Protection Act, 2019 and the Office of the Data Protection Commissioner (ODPC) may require registration and specific notices. **This must be checked with the ODPC or a qualified advisor.** This document is not legal advice. Tracked as an open item in the brief.
- Data minimization: only fields needed for an enquiry. No raw IPs stored; a salted hash supports abuse limits.
- Retention: define a period for closed enquiries and delete on schedule (period decided with the policy).
- Analytics: none, or a privacy-friendly option decided later. No cookie banner is needed if no tracking cookies are used.

## 6. Spam and abuse

Honeypot, rate limits, validation, optional Turnstile, and a stored-but-flagged path for suspicious messages. None of these rely on frontend checks alone.

## 7. Dependency and supply-chain policy

Lockfiles are committed. Dependencies are evaluated before adding (maintained, compatible, necessary). CI runs `composer audit` and `npm audit`. Installs on the build server use the lockfile only.

## 8. Incident basics

Rotate credentials, restore from backup, roll back the release. The runbook is written with DEPLOYMENT when a server exists.
