# Project Brief

Status: **Draft for approval** (Stage 2). Last updated: November 2026.

## 1. Vision

A modern, fast, accessible and SEO-friendly portfolio for Denis Kombe Karisa, backed by a simple admin panel so content can be edited without touching code. The first release is a **personal portfolio**, not a business platform.

## 2. Positioning

- Primary headline: **Software Developer | Data Analyst**
- Laravel is shown under skills and projects, not in the headline.
- Machine Learning and AI appear under **skills and projects only**, not as a commercial service yet.
- "Data with Denis" (TikTok) is shown as a content and community section, with the follower count editable in settings.
- **Overcend** is an emerging brand concept. It is not a registered or established company. Its introduction is an editable setting and stays hidden until text is provided.

## 3. Objectives

1. Establish a strong, accurate professional identity.
2. Showcase real projects and experience.
3. Generate employment and freelance enquiries through a working contact system.
4. Provide an easy admin for all content.
5. Practise professional API design, backend engineering, frontend, security, testing and DevOps.

## 4. Release 1 scope

**Included**

- Public site: Home, About, Projects (listing and detail), Services, Experience, Skills, Education and certifications, Contact, Insights (blog) once the first article exists, Privacy policy.
- Admin (single administrator, one author): Projects, categories, technologies, media, services, skills, experience, education, certifications, articles, categories and tags, contact enquiries, social links, site settings, SEO fields.
- REST API under `/api/v1` (public reads and contact submission).
- Contact form: validation, honeypot, rate limiting, optional Turnstile, stored in MySQL, queued email, enquiry status.
- Publishing pipeline: on-server queued static rebuild with locking, atomic release switch and rollback (ADR-0002).
- Draft preview through a separate authenticated mechanism (ADR-0002).
- SEO foundations, accessibility, security baseline, tests.

**Deferred (not removed, revisit later)**

Roles and permissions, testimonials, project metrics, contact message event history, audit log tables, runtime redirect management, Pagefind article search, RSS (small, may be added early), multi-language, multi-tenancy, billing, CRM, customer portals, any company (Overcend) platform.

## 5. Content integrity rules

- The legacy portfolio (`portfolioweb`) is the source of truth for existing content.
- All 20 existing projects are imported as **drafts** and reviewed one by one.
- Case-study fields (objectives, role, approach, challenges, outcomes, lessons) are **optional** and empty until the owner writes them. Empty sections are hidden.
- Nothing is invented: no outcomes, clients, statistics, testimonials or deployment claims.
- Placeholder graphics keep an `is_placeholder` flag and a visible label.
- No confidential client or government information is published. Public screenshots use synthetic or non-sensitive data.

## 6. Initial case-study candidates (recommendation)

Chosen because they have real screenshots, a source link and a clear story. The owner decides.

1. LexOps (needs sanitised detail)
2. Healthcare Patient Analytics
3. E-Commerce Sales Dashboard
4. Hotel BI Dashboard
5. Tableau HR Dashboard
6. Campus Crib (needs real screenshots)

## 7. Assumptions

These were used because the owner had not yet answered. Each can be changed.

| # | Assumption | Status |
|---|---|---|
| A1 | The headline is "Software Developer \| Data Analyst". "Tech Content Creator" lives in the Data with Denis section, not the headline. | Assumed |
| A2 | Overcend text is not yet written. The section stays hidden (`overcend_visible = false`). | Assumed |
| A3 | `datasets/` CSV files and `.pbix` files are **not** migrated into the platform media library. They stay in the legacy repo or move to dedicated GitHub repos after a licence and privacy review. | Assumed |
| A4 | The six case-study candidates above are the initial list. | Assumed |
| A5 | The Social Media dashboard screenshot contains a different phone number and email. It is imported as a draft and blocked from publishing until the owner confirms. | Assumed |
| A6 | Contact details (email, phone, WhatsApp) live only in settings, never hardcoded. | Decided |
| A7 | PHP 8.4 is used provisionally. Compatibility of Laravel 13, Filament 5, Livewire 4 and all packages is verified in Stage 4 before any production use. | To verify |
| A8 | The owner develops on Windows with VS Code and PowerShell (seen in screenshots). | Verified from screenshots |
| A9 | No domain, VPS, email provider or paid service is purchased yet. | Decided (cost) |

## 8. Open questions for the owner

1. Overcend: what should the introduction say, and where should it appear?
2. Are the six case-study candidates right? Which can you write role, approach and outcomes for?
3. Is `HumanResources-Kenyan.csv` synthetic? (Affects whether it may stay public anywhere.)
4. Social Media dashboard screenshot: update, blur or keep the contact details?
5. Does your current email (`deniskombe5@gmail.com`) remain the public contact, or will a domain email be created later?
6. Kenya data protection: you will store enquiry data. Registration and policy obligations must be checked with the Office of the Data Protection Commissioner (ODPC). This project does not provide legal advice.

## 9. Success criteria for release 1

- Parity checklist in ROADMAP passes on staging.
- Owner can add, edit, reorder, publish and unpublish every content type without code.
- Publishing a change updates the public site reliably, and a failed build never breaks the live site.
- Contact enquiries arrive, are stored, and notify by email.
- Lighthouse targets (performance 90+, accessibility 95+, SEO 95+) are measured on staging, not assumed.
- Backend tests pass; security checklist reviewed.
