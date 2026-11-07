# ADR-0009: Version and dependency policy

Status: Accepted

## Context
Laravel 13 is the current major release and requires PHP 8.3 or newer. Filament 5 and Livewire 4 are current. PHP 8.5 is available but compatibility across all packages is not yet verified.

## Decision
- Use **PHP 8.4 provisionally**. Verify Laravel 13, Filament 5, Livewire 4 and every selected package on it in Stage 4, and record exact versions.
- Use supported stable releases only; commit `composer.lock` and `package-lock.json`.
- Add a dependency only if it is necessary, maintained and compatible, and record the reason.
- Pin the Astro and Node versions at the start of Stage 6.
- Revisit PHP 8.5 after the stack is verified.

## Consequences
Predictable builds. Some verification work up front, which prevents rework later.
