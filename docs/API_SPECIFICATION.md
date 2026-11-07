# API Specification

Status: **Draft for approval** (Stage 2). Base path: `/api/v1`. Format: JSON. Same-origin in production, so no CORS is needed. For local development only, CORS allows the Astro dev server origin.

The OpenAPI document is generated from the code with a tool to be verified in Stage 5 (Scramble is the candidate). This file is the design contract that the generated document must match.

## 1. Conventions

- Only **published** content is ever returned. Drafts, soft-deleted rows and hidden items are never serialized.
- Successful responses use `{ "data": ..., "meta": ..., "links": ... }` (Laravel API Resource shape). Lists are paginated.
- Query parameters are validated. Invalid values return `422`, never a silent fallback.
- Timestamps are ISO 8601 UTC. Dates without time are `YYYY-MM-DD`.
- Text fields named `*_markdown` or listed as Markdown are returned as raw Markdown. The frontend renders them with a sanitizing pipeline. HTML is never returned from the API.

### Errors

| Status | When | Body |
|---|---|---|
| 404 | Unknown or unpublished slug | `{ "message": "Not found." }` |
| 422 | Validation failure | `{ "message": "...", "errors": { "field": ["..."] } }` |
| 429 | Rate limit exceeded | `{ "message": "Too many requests." }` plus `Retry-After` header |
| 500 | Unexpected error | `{ "message": "Server error." }` (no stack trace, no details) |

## 2. Public read endpoints

| Method and path | Purpose |
|---|---|
| `GET /profile` | Site settings: name, headline, intro, bio, contact details, social links, follower label, Overcend block (only if visible), portrait and CV URLs |
| `GET /projects` | Paginated list of published projects |
| `GET /projects/{slug}` | One published project with full detail |
| `GET /services` | Published services with items and technologies |
| `GET /skills` | Skill categories with skills |
| `GET /experiences` | Published experiences with highlights and technologies |
| `GET /education` | Published education entries |
| `GET /certifications` | Published certifications |
| `GET /articles` | Paginated published articles (empty until the first article) |
| `GET /articles/{slug}` | One published article |
| `GET /technologies` | Technologies with type and featured flag (for filters and the marquee) |

`GET /testimonials` is reserved for a later release and returns `404` until then.

### GET /projects query parameters

| Param | Type | Notes |
|---|---|---|
| `category` | slug | filter |
| `technology` | slug | filter |
| `featured` | boolean | filter |
| `sort` | `sort_order` (default), `-published_at`, `title` | allow-listed |
| `per_page` | 1 to 50, default 12 | the frontend build uses 50 |
| `page` | integer | |

### Project list item (example shape)

```json
{
  "slug": "healthcare-patient-analytics",
  "title": "Healthcare Patient Analytics",
  "summary": "An end-to-end analysis ...",
  "category": { "slug": "dashboards", "name": "Dashboards" },
  "status_label": null,
  "is_featured": true,
  "technologies": [ { "slug": "power-bi", "name": "Power BI", "type": "tool" } ],
  "cover": {
    "original_url": "/storage/media/12/cover.png",
    "width": 1280, "height": 800, "mime": "image/png",
    "alt": "Screenshot of ...", "is_placeholder": false
  },
  "published_at": "2026-11-10T09:00:00Z"
}
```

### Project detail adds

`problem`, `objectives`, `role`, `approach`, `challenges`, `outcomes`, `lessons`, `description_markdown`, `public_note`, `link_note`, `features[]`, `links[] {label, url, type}`, `gallery[]` (same shape as `cover`), `downloads[] {label, url, mime, size}`, `seo {title, description}`. Fields that are empty are returned as `null` or `[]`, and the frontend hides the matching section.

## 3. Contact submission

`POST /contact`

| Field | Rules |
|---|---|
| `name` | required, 2 to 120 |
| `email` | required, valid email, max 190 |
| `organisation` | optional, max 160 |
| `enquiry_type` | required, one of the allowed values |
| `subject` | required, 3 to 190 |
| `message` | required, 10 to 5000 |
| `budget_range` | optional, allow-listed values |
| `privacy_accepted` | required, must be true |
| `website` | **honeypot**, must be empty |
| `cf-turnstile-response` | required only when Turnstile is enabled in config |

Behaviour, in order:

1. Validate (server side, always).
2. Check honeypot and the optional Turnstile token. A tripped honeypot returns a normal-looking `201` and stores nothing, so bots learn nothing.
3. Apply rate limits (initial values, tunable): 5 requests per hour per IP, and 3 per hour per email address.
4. Store in `contact_messages` with status `new` inside a transaction.
5. Queue the notification email. Email failure never loses the stored enquiry.
6. Return `201 { "data": { "reference": "01J..." }, "message": "Thanks, your message was received." }`.

Logging records the reference and outcome, not the message body or the raw IP.

## 4. Internal endpoints (not public, not in the public docs)

| Endpoint | Purpose | Protection |
|---|---|---|
| `GET /internal/preview/{type}/{id}` | Draft content for the preview route | Valid signed token (single record, short expiry) **and** it is never reachable without it |

There is **no public webhook** to trigger rebuilds. Rebuilds start from the authenticated admin or from model events.

## 5. Rate limits (initial)

| Scope | Limit |
|---|---|
| Public GET | 120 per minute per IP |
| Contact POST | 5 per hour per IP, 3 per hour per email |
| Admin login | 5 attempts per minute per account and IP |

The build process reads the API over the internal loopback address and is exempt from the public GET limit by network rule, not by a secret.

## 6. Caching

Public GET responses send `Cache-Control` and `ETag`. Because the public site is static, runtime API traffic is low, so application-level caching is only added if measurements justify it.

## 7. Tests (Stage 5)

Every endpoint: success shape, pagination, filters, validation errors, 404 for drafts and soft-deleted rows, rate limiting, honeypot behaviour, and absence of unpublished data in any response.
