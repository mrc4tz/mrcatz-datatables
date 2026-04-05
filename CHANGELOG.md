# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.23.6] - 2026-04-05

### Added
- `MrCatzDataTables::enableAutoExpand()` — builds an expand view automatically from every plain `withColumn()` (columns with a real `$key` and no `type`). Skips index, image, action and custom callback columns. Useful when you want the mobile "more details" drawer without manually listing fields.
- Automatic fallback in `MrCatzDataTablesComponent::setData()`: if a component sets `$expandableRows` to `'mobile'`/`'desktop'`/`'both'` but never calls `enableExpand()` in `setTable()`, `enableAutoExpand()` is invoked for you. Tables get a free expand drawer by flipping a single property.

## [1.23.5] - 2026-04-05

### Added
- `withCustomColumn()` gains a new `$type` option. Pass `type: 'action'` to route a custom callback column into the mobile card's top-right actions slot, same placement as `withActionColumn()`. This is the escape hatch for callers who need a custom pre-render step (for example, fetching related data and mutating `$data` before calling `getActionView()`) but still want the column to behave as an action column on mobile.
- `withActionColumn()` is now a thin wrapper over `withCustomColumn(..., type: 'action')`, removing the duplicated `dataTableSet` mutation.

## [1.23.4] - 2026-04-05

### Fixed
- Mobile card view no longer treats every `withCustomColumn()` without a `$key` as an action column. Previously, custom columns without a data key (status badges, computed display cells, etc.) were rendered in the top-right actions slot alongside the real edit/delete buttons. Classification now checks for `getColumnType() === 'action'` — a tag set exclusively by `withActionColumn()` — so display-only custom columns flow into the card body as normal pills.

### Changed
- `withActionColumn()` now tags its column with `type = 'action'` on `dataTableSet`, mirroring how `withColumnImage()` tags image columns.

### Migration
- Tables still using the legacy `->withCustomColumn('Aksi', fn ($d, $i) => MrCatzDataTables::getActionView(...))` pattern will no longer appear in the mobile card's top-right actions slot (they'll render as body pills instead). Migrate to `->withActionColumn()` to restore the top-right placement and the keyboard shortcuts.

## [1.23.3] - 2026-04-05

### Added
- `MrCatzDataTables::withActionColumn(string $head = 'Aksi', bool $editable = true, bool $deletable = true)` — registers the built-in edit/delete action column AND records `hasEditAction` / `hasDeleteAction` on the engine so the rest of the UI can react to which actions are actually exposed.

### Fixed
- Keyboard shortcuts (Enter = edit, Delete/Backspace = delete) are now only bound when the table has a corresponding action available. Previously, `enableKeyboardNav` always wired Enter/Delete/Backspace regardless of whether edit/delete actions were exposed, which caused read-only tables (no `Aksi` column, or `editable: false` / `deletable: false`) to still open the form modal when a focused row received Enter.
- Keyboard hint row in the toolbar now hides the `Enter` / `Del/⌫` hints when the corresponding action is unavailable.

### Migration
- Tables currently using `->withCustomColumn('Aksi', fn ($d, $i) => MrCatzDataTables::getActionView($d, $i, $editable, $deletable))` should switch to `->withActionColumn(editable: $editable, deletable: $deletable)` so keyboard shortcuts light up automatically. The old form still works for backward compat but leaves the engine unaware of the action buttons, which disables the shortcuts.

## [1.23.2] - 2026-04-05

### Changed
- `MrCatzDataTableFilter::create()` and `createWithCallback()` now accept `string|iterable` for `$data` (was `string|array`). Any Traversable is accepted, including Laravel `Collection`.
- Filter data items are now auto-normalized to associative arrays inside `get()`. Callers can pass raw `DB::table(...)->get()` (Collection of `stdClass`), `Model::all()` (Collection of Models), arrays of `stdClass`, or arrays of arrays — all work without manual casting. Previously, passing a Collection of `stdClass` caused `Cannot use object of type stdClass as array` in the filter view.

## [1.23.1] - 2026-04-05

### Fixed
- `setSearchWord()` now accepts `?string` and coerces `null` to empty string in both `MrCatzDataTables` and `MrCatzDataTablesComponent`. Prevents `TypeError` when a row column used by `withColumn()` contains `NULL` in the database — the internal pluck path in `getData()` previously passed raw null values into the strict-typed `setSearchWord()`.

## [1.3.0] - 2026-04-02

### Added
- Loading skeleton placeholder rows during data fetch (replaces spinner)
- Column visibility toggle — hide/show columns via dropdown, persistent in URL (`col_hidden`)
- Inline editing — double-click editable cells, dispatches `inlineUpdateData` event to Page component
- `withColumn()` new `editable` parameter for inline edit support
- `onInlineUpdate($rowData, $columnKey, $newValue)` override-able hook on Page component
- Multi-sort — Shift+click column headers to sort by multiple columns, with numbered badges
- `addSort($key, $order)` method and `multiSort` URL-persistent state
- `setMultiSort()` on engine for multi-column ordering
- Sticky header — `$stickyHeader = true` to keep thead visible on scroll
- Row click hook — `onRowClick($data)` override-able method on Table component
- Search debounce validation — auto-corrects invalid `typeSearchDelay` format on mount
- `$enableColumnVisibility` property to show/hide column toggle button
- `$stickyHeader` property
- `MrCatzEvent::INLINE_UPDATE` constant
- Lang key `col_visibility` (EN: "Columns", ID: "Kolom")

## [1.2.5] - 2026-04-02

### Fixed
- Reset button now also clears column order

## [1.2.4] - 2026-04-02

### Fixed
- Preserve dependent filter URL params on boot — snapshot before onFilterChanged mutates

## [1.2.3] - 2026-04-02

### Fixed
- Restore dependent filter values after onFilterChanged on boot

## [1.2.2] - 2026-04-02

### Fixed
- Trigger onFilterChanged on mount when filters loaded from URL

## [1.2.1] - 2026-04-02

### Changed
- Column reorder persisted via URL (`col_order`) instead of localStorage

## [1.2.0] - 2026-04-02

### Added
- Default PDF export blade view (`mrcatz::exports.datatable-pdf`) — no longer requires user to create template
- Accessibility: `aria-sort` on sortable column headers, `aria-modal` + `aria-labelledby` on all modals
- Accessibility: focus trap (`x-trap`) on all modals (export, reset, bulk delete, form, delete confirm)
- Accessibility: `aria-label` on bulk checkboxes (header + per-row), `aria-live` on toast container
- Accessibility: `role="grid"` + `aria-label` on data table
- Export hooks: `beforeExport($headers, $rows, $format, $scope)` and `afterExport($format, $scope)`
- Column reorder persistence via URL query parameter `col_order` (`#[Url]`)
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

[Unreleased]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.3.0...HEAD
[1.3.0]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.5...v1.3.0
[1.2.5]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.4...v1.2.5
[1.2.4]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.3...v1.2.4
[1.2.3]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.2...v1.2.3
[1.2.2]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.3...v1.2.0
[1.1.3]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/mrc4tz/mrcatz-datatables/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/mrc4tz/mrcatz-datatables/releases/tag/v1.0.0
