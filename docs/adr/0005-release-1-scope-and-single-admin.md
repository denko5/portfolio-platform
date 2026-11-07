# ADR-0005: Release 1 scope and single administrator

Status: Accepted

## Context
The original specification lists about 25 tables and many features. There is one administrator and one author.

## Decision
Release 1 includes only what the public portfolio and reliable publishing need (see PROJECT_BRIEF). Deferred: roles and permissions, testimonials, project metrics, contact message events, audit log tables, runtime redirects. Each is added by a migration when a real need appears.

## Consequences
Faster delivery and less to secure and maintain. Basic authentication events are still logged. A testimonials section is not built until real testimonials exist.
