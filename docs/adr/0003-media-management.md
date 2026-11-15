# ADR-0003: Media management with Spatie and Astro

Status: Proposed. Verify Spatie Media Library and its Filament plugin with Filament 5 and Laravel 13 in Stage 4.

## Context
Projects, articles and settings need images and files with metadata (alt text, placeholder flag). Public images must be responsive (AVIF or WebP, srcset). Two systems must not both process public images.

## Evaluation of Spatie Media Library

| Pros | Cons |
|---|---|
| Collections, custom properties, cleanup on delete, widely used | Extra package and `media` table |
| Official Filament integration exists for earlier versions | Compatibility with Filament 5 and Laravel 13 is **unverified** |
| Metadata (alt, `is_placeholder`) fits custom properties | Polymorphic by design (the one justified exception to our rule against polymorphism) |

## Decision
Use Spatie Media Library **only to store the original files**. Per-image metadata (`alt`, `is_placeholder`, `caption`, order) lives in a `project_images` table as real columns, because Filament's core file upload has no per-file metadata and the official Spatie plugin only sets custom properties at upload time (checked in the plugin documentation, November 2026). A third-party metadata plugin was considered and rejected to avoid an extra dependency. Public responsive variants are created by **Astro at build time**.

## How Astro accesses media
1. The API returns each image with `original_url`, `width`, `height`, `mime`, `alt`, `is_placeholder` (alt and placeholder come from `project_images`).
2. The build downloads each original once into a local cache keyed by URL plus a content hash (over loopback).
3. Astro's image pipeline creates AVIF and WebP variants and `srcset` from the cached originals, with explicit width and height.
4. SVG placeholders pass through unchanged (no raster conversion).
5. Variants end up inside the static release, so rollback restores the exact images too.

Result: Laravel owns originals, Astro owns public variants. No duplicated processing.

## Spike status
- Spike 1, dependency resolution: **passed** (14 November 2026). Spatie Media Library 11.23.13 and the Filament Spatie plugin 5.11.3 resolve together with Filament 5.11.3 on Laravel 13.42.0 and PHP 8.4.27.
- Spike 2, runtime: **pending**. Must prove upload, `alt` and `is_placeholder` custom properties, API exposure of originals, and Astro build access. The ADR stays Proposed until this passes.

## Fallback
If Spatie is not compatible, use a small own table (`media_assets`: path, alt, is_placeholder, width, height, sort order) with Filament's file upload. This avoids the polymorphic relation and is acceptable for the small number of image-bearing models.

## Consequences
First builds are slower until the cache warms. The image cache is a build concern and is ignored by Git.
