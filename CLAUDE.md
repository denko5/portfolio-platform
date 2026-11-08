# CLAUDE.md: instructions for AI-assisted development

## Project
Content-managed portfolio platform for Denis Kombe Karisa. Decoupled monorepo:
`backend/` (Laravel 13 + Filament 5 + MySQL 8) and `frontend/` (Astro static site).
Read `docs/PROJECT_BRIEF.md` and `docs/ARCHITECTURE.md` before changing anything.

## Hard rules
1. Work only inside the **approved stage** (see `docs/ROADMAP.md`). Stop and ask before moving on.
2. **Never invent content**: employment, outcomes, statistics, clients, certifications, testimonials, deployment claims.
   Missing facts stay empty and hidden. Ask the owner.
3. **Never claim a feature is done** unless it is implemented and its tests have been run. Report real results.
4. The legacy site repo (`portfolioweb`) is **read-only reference**. Do not modify it.
5. No secrets in Git. Use `.env` (ignored). Provide `.env.example` only.
6. No em dashes or en dashes anywhere (code, docs, copy). Use commas, colons, or "to" for ranges.
7. Do not add a dependency without recording why in the PR or an ADR (maintained? compatible? necessary?).
8. Do not make destructive production changes without a backup and recovery plan.
9. Overcend is an **emerging brand concept**. Never describe it as a registered or established company.
10. Unpublished content must never be reachable from the public API or the public site.
11. Use the neutral name "Legal Operations and Case Management Platform". Do not imply County ownership, endorsement or production deployment.
12. Do not import or publish `HumanResources-Kenyan.csv`, any `.pbix` file or patient information. Block the Social Media dashboard screenshot until corrected.
13. Fonts: Fraunces (headings) and Inter (body), self-hosted. See `docs/DESIGN.md`.

## Conventions
- PHP: Laravel Pint formatting; Pest tests; Form Requests for validation; API Resources for output.
- Database: migrations only (never edit production data by hand); foreign keys, unique constraints and indexes as in `docs/DATABASE.md`.
- API: versioned under `/api/v1`; shapes defined in `docs/API_SPECIFICATION.md`.
- Frontend: Astro + TypeScript, semantic HTML, minimal JS, accessible by default (keyboard, focus, reduced motion).
- Commit messages: imperative, small, one concern per commit.

## Commands (filled in during Stage 4)
- Backend tests: `cd backend && php artisan test` (or `./vendor/bin/pest`)
- Backend format: `cd backend && ./vendor/bin/pint`
- Frontend build: `cd frontend && npm run build`

## Directory map
```
backend/   Laravel app: Filament admin (/admin), JSON API (/api/v1), queue jobs
frontend/  Astro static site; reads the API at build time
deploy/    Nginx and Supervisor config, deployment scripts
docs/      Project documentation and ADRs
```

## When unsure
Ask. State what is verified, what is assumed, and what you recommend.
