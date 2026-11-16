# Roadmap

Status: **Draft for approval.** Stages follow the agreed workflow. Each stage ends with a review. Estimates are relative sizes (S, M, L), not dates.

## Principles

- Public portfolio experience and reliable publishing come before extras.
- The current site stays live until the new platform passes the parity checklist.
- Nothing is called done until it is implemented and verified.

## Stages

| Stage | Goal | Size | Exit criteria |
|---|---|---|---|
| 1 Discovery | Verified inventory of the legacy site | done | Report approved |
| 2 Documentation | This doc set and ADRs | S | Owner approves docs and answers open questions |
| 3 Design | Wireframes, design system derived from the current site, ERD final | M (layouts approved, fonts chosen) | Owner approves home, projects, project detail, about, contact, insights (desktop and mobile) |
| 4 Backend foundation | Laravel 13, MySQL, migrations, seeders, Filament, 2FA, legacy import (drafts), CI | L | Tests pass; all 20 projects imported as drafts; admin can edit every release-1 content type |
| 5 API | Endpoints, validation, resources, rate limits, docs, tests | M | Contract tests pass; unpublished data never leaks |
| 6 Frontend | Astro pages, components, client-side filters, contact form | L | Pages built from API; accessible; responsive 320 to 1440 |
| 6b Publishing | Queue rebuild, lock, atomic release, rollback, preview | M | Failure never breaks the live release; rollback proven locally |
| 7 Quality | Tests, security checks, accessibility, performance audit | M | Lighthouse and checklist results recorded honestly |
| Parity gate | Parity checklist passes | S | Owner signs off |
| 8 Deployment | Staging, then production, backups, monitoring | L | **Paused until funded** |

Design (Stage 3) can overlap with the start of Stage 4. The backend foundation does not depend on visual decisions.

## Critical path to parity

Stage 2 approval, Stage 3 design approval, Stage 4 backend, Stage 5 API, Stage 6 frontend plus publishing, content review (the owner's work), Stage 7, parity gate.

Owner work that can run in parallel: review the 20 imported projects, write case studies, confirm facts, supply Overcend text, decide email and domain.

## Stage 4 progress

- [x] Local tools verified (PHP 8.4.27, Composer, Node)
- [x] Laravel 13.42.0 skeleton, Filament 5.11.3 admin, Spatie media, Pest, Scramble installed (dependency resolution verified, ADR-0009)
- [x] Admin login works locally
- [x] Migrations, enums and models from DATABASE.md (tests passing)
- [x] Admin access allow-list and 2FA (verified in browser)
- [x] Projects resource with images, publishing hold (uploads verified in browser)
- [ ] Legacy import of projects as drafts (written, awaiting local run)
- [ ] Import of services, skills, experience, education, certifications and settings
- [ ] Other Filament resources (services, skills, experience, settings, enquiries)
- [ ] CI with MySQL 8 (ADR-0010)

## Stage 4 task outline

1. Verify local tools (PHP 8.4, Composer, Node). Docker and local MySQL are deferred (ADR-0010).
2. Create Laravel 13 app in `backend/`, verify Laravel 13, Filament 5, Livewire 4, PHP 8.4 compatibility, record versions.
3. Migrations from DATABASE.md, models, factories.
4. Spatie media decision confirmed or replaced (ADR-0003 spike).
5. Filament resources, then 2FA.
6. Legacy import command (drafts), with the import checks in DATABASE.md.
7. Pest tests, Pint, CI workflow.

## Parity checklist (must pass before cutover)

**Content**
- [ ] 20 projects reviewed and either published or deliberately left draft
- [ ] All images have alt text; placeholders still labelled
- [ ] 7 services, skills, 3 experiences, 2 education entries, 3 certifications present
- [ ] CV downloadable; portrait and OG image present
- [ ] Social links, WhatsApp and email come from settings and work
- [ ] Follower count editable in admin
- [ ] Data with Denis section with covers
- [ ] Headline is "Software Developer | Data Analyst"; Overcend hidden unless text exists
- [ ] Open data issues resolved (duplicate Colab link, Social Media screenshot contacts)

**Features**
- [ ] Project filters (category, technology) and details, including multi-image galleries
- [ ] Marquee of featured technologies (respecting reduced motion)
- [ ] Contact form stores the enquiry, emails the owner, shows confirmation, handles errors
- [ ] Privacy policy page exists and is linked from the form
- [ ] Custom 404 page

**Quality**
- [ ] Responsive at 320, 375, 425, 768, 1024, 1440 with no horizontal scroll
- [ ] Keyboard navigation, visible focus, reduced motion, skip link
- [ ] Meta, Open Graph, canonical, sitemap, robots, structured data, project content present **in the HTML**
- [ ] Lighthouse results recorded (targets: performance 90+, accessibility 95+, SEO 95+)
- [ ] Core Web Vitals checked on a throttled mobile profile
- [ ] Security checklist reviewed; no unpublished data reachable
- [ ] Backend tests pass; frontend build passes; no em or en dashes
- [ ] Publish, failed-build and rollback paths demonstrated

## Cutover plan (when funded)

1. Staging deployment passes the full checklist on a real server.
2. Content freeze on the legacy site for a short window.
3. Production deployment, then DNS change.
4. Keep the legacy site available as a fallback until the new one is stable.
5. Update canonical URLs, sitemap and social profile links to the new domain.
6. Retire the legacy deployment only after the owner confirms.

## Risks

| Risk | Mitigation |
|---|---|
| Owner content (case studies, Overcend) not ready | Optional fields, hidden sections, launch without them |
| SQLite and MySQL behave differently | MySQL 8 runs in CI on every push; real local MySQL before Stage 5 exits (ADR-0010) |
| Dependency incompatibility (PHP 8.4, Filament 5, Livewire 4, Spatie) | Verify in Stage 4 before building on them; documented fallbacks in ADRs |
| Astro build memory on a small VPS | Measure locally, use build timeouts and low priority, size the VPS accordingly |
| Scope creep into a company platform | Overcend stays out of release 1 |
| Budget delays server work | Local production-like setup keeps learning and verification moving |
