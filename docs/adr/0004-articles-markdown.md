# ADR-0004: Articles stored as Markdown

Status: Accepted

## Context
Arbitrary stored HTML is an XSS risk and hard to sanitize consistently.

## Decision
Articles (and long project case-study text) are stored as **Markdown**. The API returns Markdown. Astro renders it with its Markdown pipeline plus sanitization so raw HTML is not allowed. Filament uses its Markdown editor.

## Consequences
Safer output and simple content. Rich embeds are limited to what the Markdown pipeline supports. The blog launches empty and stays hidden until the first article is published.
