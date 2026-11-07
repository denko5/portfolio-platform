# Portfolio Platform

A content-managed professional portfolio for **Denis Kombe Karisa** (Software Developer | Data Analyst).

| Part | Technology | Folder |
|---|---|---|
| Public website | Astro (static generation), TypeScript, modern CSS | `frontend/` |
| API and admin | Laravel 13, Filament 5, MySQL 8 | `backend/` |
| Documentation | Markdown, Mermaid diagrams | `docs/` |
| Deployment assets | Nginx, Supervisor, scripts | `deploy/` |

## Status

**Stage 2 (documentation) complete, awaiting approval. No application code exists yet.**

The current portfolio (`github.com/denko5/portfolioweb`, GitHub Pages) **stays live and untouched** until this platform reaches the parity checklist in [docs/ROADMAP.md](docs/ROADMAP.md).

Deployment to a server is **paused** (cost). Everything is developed and tested locally first.

## Documentation

| Document | Purpose |
|---|---|
| [PROJECT_BRIEF](docs/PROJECT_BRIEF.md) | Vision, scope, assumptions, open questions |
| [ARCHITECTURE](docs/ARCHITECTURE.md) | System design, diagrams, request and publish flows |
| [DATABASE](docs/DATABASE.md) | ERD, tables, constraints, content migration mapping |
| [API_SPECIFICATION](docs/API_SPECIFICATION.md) | Versioned REST API contract |
| [SECURITY](docs/SECURITY.md) | Controls, CSP plan, privacy, uploads |
| [DEPLOYMENT](docs/DEPLOYMENT.md) | Environments, local production-like setup, paused server plan |
| [ROADMAP](docs/ROADMAP.md) | Stages, exit criteria, parity checklist, cutover plan |
| [adr/](docs/adr/README.md) | Architecture decision records |

## Working rules

See [CLAUDE.md](CLAUDE.md). In short: work in approved stages, never invent content, never claim completion without verified tests.

## Local development

Not available yet. Instructions are added in Stage 4 when the backend and frontend exist.
