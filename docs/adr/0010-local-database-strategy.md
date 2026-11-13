# ADR-0010: Local database strategy without Docker

Status: Accepted (interim). Revisit when Docker or a local MySQL 8 is available.

## Context
Production targets MySQL 8. The owner's machine has XAMPP, which ships MariaDB, not MySQL 8, and Docker is not being used for now (decision recorded in November 2026). Installing MySQL natively would add a service that competes with XAMPP on port 3306.

## Decision
1. **Early local development and tests use SQLite.** Laravel supports it out of the box and it needs no setup.
2. **MySQL 8 is verified in CI.** GitHub Actions runs the Pest suite against a MySQL 8 service container on every push. A change is not accepted until it passes on MySQL. (Check the repository plan's free CI allowance.)
3. **Real MySQL 8 locally before Stage 5 exits and before any staging.** Docker Compose, or another local MySQL 8, is added then. The compose file is prepared but not required now.
4. Migrations stay **portable**: no raw SQL, no database-specific functions, no full-text or JSON columns.

## Known differences to watch
Case sensitivity and collation of unique slugs, index key lengths with utf8mb4, strict mode and default values, foreign key enforcement, and date handling. These are exactly what the MySQL CI run exists to catch.

## Consequences
Fastest start with no extra install, at the cost of a delayed local MySQL check. The CI safeguard keeps that risk visible. Backups, restores and performance work still require real MySQL later.
