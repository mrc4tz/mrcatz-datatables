# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-04-02

### Added
- Default PDF export blade view (`mrcatz::exports.datatable-pdf`) — no longer requires user to create template
- Accessibility: `aria-sort` on sortable column headers, `aria-modal` + `aria-labelledby` on all modals
- Accessibility: focus trap (`x-trap`) on all modals (export, reset, bulk delete, form, delete confirm)
- Accessibility: `aria-label` on bulk checkboxes (header + per-row), `aria-live` on toast container
- Accessibility: `role="grid"` + `aria-label` on data table
- Export hooks: `beforeExport($headers, $rows, $format, $scope)` and `afterExport($format, $scope)`
- Column reorder persistence via localStorage (survives page refresh)
- New README sections: Export Hooks, Column Reorder Persistence, PDF Export, Accessibility

### Changed
- PDF export now falls back to package view if `exports.datatable-pdf` doesn't exist in user's project

## [1.1.3] - 2026-04-02

### Fixed
- `mrcatz_lang()` now uses `config('mrcatz.locale')` instead of `app()->getLocale()`

## [1.1.2] - 2026-04-02

### Fixed
- Remove strict types from all public properties for backward compatibility — child classes can override properties without type declaration

## [1.1.1] - 2026-04-02

### Changed
- Remove duplicate translation arrays (`en`, `id`) from `config/mrcatz.php` — config now only stores `locale` setting
- Localization fully handled by Laravel lang files (`lang/vendor/mrcatz/`)
- `mrcatz_lang()` helper normalizes replacement keys (both `:key` and `key` formats work)

### Fixed
- `mrcatz_lang()` graceful fallback when translator service is unavailable

## [1.1.0] - 2026-04-02

### Added
- GitHub Actions CI/CD workflow for automated testing on PHP 8.1–8.4
- Integration tests via `orchestra/testbench` with SQLite in-memory database (71 tests, 168 assertions)
- Default `MrCatzExport` class inside package (no longer requires `\App\Exports\DatatableExport`)
- Laravel lang files for localization (`lang/en/mrcatz.php`, `lang/id/mrcatz.php`)
- Publishable lang files via `php artisan vendor:publish --tag=mrcatz-lang`
- Troubleshooting/FAQ section in README
- Graceful localStorage fallback for filter presets in private browsing
- `CHANGELOG.md`

### Changed
- Localization now uses Laravel lang files with config fallback
- Export uses package's built-in `MrCatzExport` class by default, falls back to `\App\Exports\DatatableExport` if exists

## [1.0.2] - 2026-04-02

### Fixed
- Backward compatibility on override-able methods — removed strict return types and parameter types from `saveData()`, `dropData()`, `prepareEditData()`, `baseQuery()`, `setTable()`, `setFilter()`, `onFilterChanged()`, etc. to prevent `Declaration compatibility` errors in existing projects

## [1.0.1] - 2026-04-02

### Added
- `MrCatzEvent` constants class — replaces all magic string event names (`'refresh-data'` → `MrCatzEvent::REFRESH_DATA`)
- `HasFilters` trait — extracted filter logic from `MrCatzDataTablesComponent`
- `HasExport` trait — extracted export logic from `MrCatzDataTablesComponent`
- `HasBulkActions` trait — extracted bulk selection logic from `MrCatzDataTablesComponent`
- PHPUnit test suite (44 tests, 128 assertions)
- `phpunit.xml` configuration
- `.gitignore` file
- PHPUnit as dev dependency

### Changed
- `MrCatzDataTablesComponent` reduced from 517 to ~170 lines using traits
- Added PHP 8.1+ type hints on internal (non-override-able) methods and properties
- `MrCatzDataTables` — strict types on fluent API methods
- `MrCatzDataTableFilter` — strict types on properties and factory methods

## [1.0.0] - 2026-04-02

### Added
- Initial stable release
- Full CRUD lifecycle with Livewire events
- Multi-keyword relevance search with highlighting
- Filter system (simple, callback, dependent filters)
- Filter presets with localStorage persistence
- URL state persistence for search, sort, filter, pagination
- Bulk actions with conditional per-row selection
- Export to Excel (.xlsx) and PDF (.pdf)
- Keyboard navigation (Arrow, Enter, Delete, Escape)
- Column resize, reorder, and sorting
- Expandable rows with inline detail views
- Responsive pagination (mobile/desktop)
- Toast notification system
- Artisan generators (`mrcatz:make`, `mrcatz:remove`)
- Multi-language support (English, Indonesian)
- DaisyUI 5 / Tailwind CSS styling

[Unreleased]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.3...v1.2.0
[1.1.3]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/mrc4tz/mrcatz-datatables/releases/tag/v1.0.0
