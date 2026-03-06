# Changelog

All notable changes to this project will be documented in this file.

## [0.1.1] - 2026-03-06

### Changed

- Switched package metadata to `statamic-addon` and added `extra.statamic` details for proper Statamic addon discovery.
- Updated service provider lifecycle to use addon boot flow (`bootAddon`) so tag/modifier registration is handled reliably by Statamic.
- Improved TOC internals for stronger compatibility with existing `<s:toc>` usage and legacy output keys (`toc_id`, `toc_title`, `children`, `has_children`).
- Extended type declarations and PHPDoc annotations across tag/modifier/provider/cache/service code to satisfy strict static analysis requirements.

### Fixed

- Fixed tag discovery/runtime issue that caused `Could not find files to load the toc tag` in integrated projects.
- Fixed return-type compatibility issues in tag methods used by Statamic Blade tag rendering.
- Fixed Bard traversal duplication edge case in nested content extraction.
- Fixed DOM heading attribute handling to avoid incorrect nullsafe access.

### Added

- Added PHPStan configuration and baseline files for consistent static analysis runs.
- Added regression test coverage for tag contract behavior and compatibility guards.

### Developer Experience

- Added/updated dev dependencies for Pint, Larastan, type coverage, and extension installer.
- Cleaned temporary artifacts and expanded `.gitignore` for local generated files.

## [0.1.0] - 2026-03-05

### Added

- Initial package scaffolding for Laravel 12 + Statamic 6.
- TOC extraction service for HTML, Markdown, and Bard sources.
- Deterministic anchor generation with duplicate heading suffixing.
- HTML heading ID injection modifier.
- Statamic Tag and Modifier integration.
- Optional cache layer for TOC tag output.
- Default Blade and Antlers TOC views.
- Unit tests for core TOC behavior.
- English README with integration instructions.
