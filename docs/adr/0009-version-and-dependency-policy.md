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

## Verified resolution (14 November 2026, `composer --dry-run`, Windows, PHP 8.4.27)

| Package | Resolved version | Note |
|---|---|---|
| Laravel framework | 13.42.0 | skeleton installed and booting |
| Composer | 2.8.10 | via Herd |
| Filament | 5.11.3 | installs on Laravel 13 |
| Livewire | 4.4.8 | pulled in by Filament |
| Spatie Laravel Media Library | 11.23.13 | installs together with Filament |
| Filament Spatie media plugin | 5.11.3 | |
| Pest and pest-plugin-laravel | 4.7.8 and 4.1.0 | needs `-W`; downgrades dev-only PHPUnit 12.5.44 to 12.5.33 |
| Scramble (API docs) | 0.13.53 | installs on Laravel 13 |

This proves **dependency resolution only**. Runtime behaviour is verified as each feature is built. Windows pitfall: do not use a caret constraint such as `"^5.0"` in PowerShell commands; omit the constraint and let Composer choose.

## Consequences
Predictable builds. Some verification work up front, which prevents rework later.
