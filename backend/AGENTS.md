# Backend instructions

The project rules live in the repository root: `../CLAUDE.md`. Read them first.

- Do not install Laravel Boost, and do not run remote install scripts (`curl | bash`, `iex`), without explicit owner approval and an ADR. See root rule 7.
- Runtime: PHP 8.4, Laravel 13, Filament 5. Local database is SQLite (ADR-0010); CI runs MySQL 8.
- Commands: `php artisan test`, `./vendor/bin/pint`.
