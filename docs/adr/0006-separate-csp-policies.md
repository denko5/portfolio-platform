# ADR-0006: Separate CSP for public site and admin

Status: Accepted

## Context
The public Astro site can run with a very strict policy. Filament, Livewire and Alpine may require looser script rules.

## Decision
Nginx sends one strict policy for public pages and a different policy for `/admin` and Livewire and Filament asset paths. The admin starts in Report-Only mode and is tightened after review. Public fonts are self-hosted.

## Consequences
Strong protection where it matters most (the public site) without breaking the admin. Needs two Nginx location blocks and testing. Details in SECURITY.md.
