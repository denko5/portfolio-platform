# Deployment

Status: **Server deployment PAUSED (cost).** Local development and a free local production-like environment come first. Nothing in this file requires spending money until the owner decides.

## 1. Environments

| Environment | Status | Notes |
|---|---|---|
| Local | Stage 4 | Laravel and Astro on the developer machine, MySQL and Mailpit in Docker |
| Local production-like | Stage 4 | Nginx + PHP-FPM + MySQL in Docker Compose, same routing as production, to rehearse the real setup for free |
| Staging | **Paused** | Real server, real HTTPS, test data |
| Production | **Paused** | Public site |

The legacy site on GitHub Pages stays live throughout. No DNS change happens until the parity checklist and a staging run pass.

## 2. Local production-like setup (free)

Goal: learn and test the deployment mechanics without a server.

- `docker-compose.yml` with services: `mysql`, `mailpit` (catches emails), and an optional `web` stack (Nginx + PHP-FPM) that mirrors the production routing: `/` static files, `/api/v1` and `/admin` to Laravel, `/storage` for media.
- The publishing job, atomic release switch and rollback are exercised locally with `releases/` and `current` folders.
- Backups and restore are rehearsed against the local database.

## 3. Costs to price before going live (verify current prices)

| Item | Needed for | Notes |
|---|---|---|
| Domain name | Public address, email authentication | Yearly cost, registrar choice open |
| VPS | Hosting Laravel, MySQL, static site and the build | A Contabo VPS is the stated target; check the plan's CPU and RAM (Astro builds need memory) |
| Transactional email | Enquiry notifications | Several providers have free tiers; verify limits and terms |
| Off-server backup storage | Real backups | Usually small monthly cost; a VPS snapshot is **not** a backup |
| Monitoring and error tracking | Uptime and errors | Free tiers exist; verify |
| Cloudflare Turnstile | Optional spam layer | Free tier; verify |

## 4. Server plan (for later)

1. Provision Ubuntu LTS, create a **non-root deploy user**, **SSH keys only**, disable password login, enable the firewall (allow SSH, HTTP, HTTPS only).
2. Install Nginx, PHP-FPM (8.4 provisionally), MySQL 8, Node (for builds), Supervisor, Certbot. PHP 8.4 may come from a third-party repository on Ubuntu LTS, which is a documented step.
3. MySQL: application user with least privilege, database not reachable from the internet.
4. Nginx: static root at `current/`, proxy rules for `/api/v1`, `/admin`, `/storage`, the two CSP policies from SECURITY.
5. HTTPS with Certbot, then HSTS only after HTTPS is verified.
6. Supervisor runs the queue worker. Cron runs the Laravel scheduler.
7. Logs rotate. `APP_DEBUG=false`. Config and route caches enabled on deploy.

## 5. Release layout and rollback

```
/var/www/portfolio/
├── backend/            Laravel (deployed by CI)
├── releases/
│   ├── 20261201-1200/  built static site
│   └── 20261201-1415/
└── current -> releases/20261201-1415   (symlink, switched atomically)
```

- A new release is built in its own folder, validated, then `current` is switched atomically.
- Keep the last 5 releases. **Rollback** means pointing `current` back to the previous release (one command, also available from the admin).
- Backend deploys run migrations with `--force` only after a database backup exists.

## 6. CI/CD (GitHub Actions)

Stage 4 onward: on every push, run Pint, Pest and the frontend build. Deployment jobs are added only when a server exists, using SSH keys stored as encrypted secrets and the non-root deploy user.

## 7. Backups

- Nightly `mysqldump` (compressed, encrypted), copied **off the server**.
- Uploaded media synchronized off the server.
- **Restore tests** on a schedule. A backup that was never restored is not trusted.

## 8. Monitoring

Uptime check on the homepage and `/api/v1/profile`, error tracking, build failure alerts by email (from `site_builds`), disk space alert.

## 9. Email deliverability (when a domain exists)

- **SPF**: authorize the chosen provider's sending servers.
- **DKIM**: publish the provider's signing keys.
- **DMARC**: start with a monitoring policy, then tighten after reviewing reports.
- Check bounce handling and test delivery to major mailbox providers.
- Verify Contabo's current outbound mail restrictions (for example blocked SMTP ports) before choosing a provider. Prefer an HTTPS API provider if SMTP is restricted. Tracked in ADR-0007.

## 10. Runbook (written when the server exists)

Deploy, rollback, restore from backup, rotate secrets, renew certificates, respond to a failed build.
