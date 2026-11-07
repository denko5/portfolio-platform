# ADR-0001: Decoupled monorepo with Astro static generation

Status: Accepted

## Context
The owner wants to learn how a backend API connects to a frontend, and needs a fast, SEO-friendly site. The legacy site renders projects with client-side JavaScript, so crawlers that do not run JavaScript see no projects (verified on the live site).

## Decision
One repository with `backend/` (Laravel API and Filament admin) and `frontend/` (Astro). Astro generates static HTML at build time from published API content. Nginx serves the static files and proxies `/api/v1` and `/admin` to Laravel on the same origin. No server-side rendering unless a documented requirement exists (the preview route in ADR-0002 is the candidate exception).

## Consequences
- Pages are pre-rendered, fast and crawlable. Runtime load on PHP is tiny.
- Content changes need a rebuild, solved in ADR-0002.
- Same origin avoids CORS and cookie complexity.
- A Node toolchain is needed on the build machine.
- Alternative considered: a Laravel Blade monolith (simpler, but less API learning and a different frontend story).
