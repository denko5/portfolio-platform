# ADR-0002: Publishing pipeline and draft preview

Status: Proposed. Confirm with spikes in Stage 4 (build on the target environment) and Stage 6b (preview).

## Context
A static site must be rebuilt when content changes. Rebuilds must be safe: no public trigger, no overlapping builds, no broken live site, and a way back.

## Decision: on-server queued rebuild

**Triggers.** Saving or publishing publishable content (projects, services, skills, experience, education, certifications, articles, settings, social links) requests a rebuild. The admin also has a manual "Publish now" action.

**Authentication.** Only authenticated admin actions and model events can request a rebuild. There is **no public webhook or token endpoint**.

**Rate limiting.** The manual action is limited (for example once per minute). Model events do not each start a build because of debouncing.

**Debounce and locking.**
- A unique, delayed job (for example 60 seconds) collapses rapid edits into one build.
- A build lock (an atomic lock) ensures only one build runs. If edits arrive during a build, the job runs once more afterwards.

**Build.** The worker builds into a new `releases/<timestamp>` folder, reading **published** content from the API over loopback. A build timeout and low process priority protect the server.

**Validation before switching.** Required files exist (home page, sitemap, 404), page count is within an expected range, and the build exited cleanly.

**Atomic release switch.** `current` is a symlink replaced atomically. Visitors never see a half-built site.

**Failure handling.** On failure the live release stays untouched, `site_builds` stores the error summary, and the admin gets an email and a dashboard warning.

**Rollback.** Repoint `current` to the previous release. Available as a command and an admin action. The last 5 releases are kept.

## Decision: draft preview (separate mechanism)
The public API never returns drafts. Preview uses a **signed, short-lived, single-record token** created by an authenticated admin action. A dedicated route renders that one draft with the same Astro components, marked `noindex`, never cached, never in the sitemap.

Implementation candidate: Astro hybrid mode where only the preview route is server-rendered by a small Node process behind Nginx on loopback. This is the one documented exception to "no SSR".
**Fallback if the spike shows too much complexity:** release 1 launches without in-admin preview, and the owner previews with a local build. This keeps reliable publishing as the priority.

## Consequences
- No external service or GitHub token is needed to publish.
- Server needs Node and enough memory for builds (to be measured).
- A `site_builds` table gives visibility and history.
- Preview adds one small dynamic component, deliberately isolated.
