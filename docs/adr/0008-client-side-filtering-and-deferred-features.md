# ADR-0008: Client-side filtering, deferred search and redirects

Status: Accepted

## Context
The project catalogue is about 20 items. A static site cannot call a search API per keystroke without adding runtime dependence.

## Decision
- Project filtering and search run **client-side** over data embedded at build time.
- **Pagefind** is deferred until article search is actually needed.
- **Runtime redirect management** is deferred. The legacy site is a single page with only anchor links, so no URLs break. If a domain or URL structure changes later, redirects become Nginx rules generated at deploy time.

## Consequences
No duplicated filtering logic in the API and the frontend, and fewer moving parts. Revisit when the catalogue or blog grows.
