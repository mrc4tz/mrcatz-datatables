# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.29.21] - 2026-04-23

### Fixed
- **Pagination not resetting to page 1 when filter, search, or per-page changed on a component with a custom `setPageName()`.** All 8 internal `$this->setPage(1)` call-sites across `MrCatzDataTablesComponent` (searchData / resetData / paginate) and `HasFilters` (change / applyCheck / changeDateRange / and the other filter-state mutators) used the 2-arg `setPage($page, $pageName = 'page')` method from Livewire's `HandlesPagination` trait without passing the component's actual page name. With the v1.29.20 `setPageName()`-driven URL prefix in effect, the real paginator lives under a non-default key (e.g. `'desaPage'`), so these calls were quietly resetting the wrong paginator and leaving the user stranded on an out-of-bounds page after an applied filter — showing an empty table instead of the filtered page 1 result. Now every internal `setPage(1, ...)` passes `$this->setPageName()` so it always hits the paginator actually in use.

## [1.29.20] - 2026-04-23

### Fixed
- **URL state collision between multiple datatables on the same Livewire page.** Two `<livewire:*-table />` instances hosted on a single page used to share every query-string key (`?search=`, `?filter=`, `?col_hidden=`, `?col_order=`, `?col_widths=`, `?sort=`, `?dir=`, `?sort_multi=`, `?per_page=`), because the parent component declared them with `#[Url(as: '...')]` — a compile-time constant, same for every subclass. Hiding column 1 in table A immediately hid column 1 in table B; searching in one table drove results in both. The fix re-routes the same 9 aliases through the legacy `queryString()` method (still fully supported in Livewire 3 via `SupportQueryString::getQueryString()`), which is free to build keys at runtime from `urlPrefix()`.

### Added
- **`urlPrefix()` method** on `MrCatzDataTablesComponent`. Default implementation returns `setPageName() . '_'` when the page name has been overridden to a non-default value (anything other than `'page'` / `''` / `null`), or an empty string otherwise. Consumers can override for custom schemes (shorter prefixes, different separators) without also redefining `queryString()`.
- **`queryString()` method** on `MrCatzDataTablesComponent`. Returns the dynamic URL alias map for all 9 persisted properties, prefixed with `urlPrefix()`.

### Changed
- **Single-table pages — no change.** `setPageName()` still returns `'page'` by default, so `urlPrefix()` returns `''`, and query-string keys stay exactly as they were (`?search=`, `?filter=`, etc.). Existing bookmarks and shared URLs on single-datatable pages continue to work.
- **Multi-table pages — opt in by overriding `setPageName()` per table.** Declaring `public function setPageName() { return 'penyediaPage'; }` on one child and `'swakelolaPage'` on the other routes their URL state to `?penyediaPage_search=…` and `?swakelolaPage_search=…` respectively. Pagination already uses the same page name, so the namespace is consistent across all state.
- Removed the 9 `#[Url(...)]` attributes and the `use Livewire\Attributes\Url;` import from `MrCatzDataTablesComponent.php`. The property declarations themselves are unchanged so child-class overrides stay source-compatible.

### Migration notes
- **If you currently have two datatables on one page and had been living with the collision:** override `setPageName()` on each child component. No other code changes are required; the package rewrites the aliases on the next request. Old bookmarks that hit the collided URLs won't rehydrate — they pointed at a bug and there's no backwards-compatible way to preserve them without reintroducing the collision.
- **If you already override `setPageName()` on a table for pagination purposes only:** your pagination URL (`?yourPageName=2`) is unchanged, but the *other* URL params now prefix onto the same name (`?yourPageName_search=foo`). Shared links that passed both `?yourPageName=…` and the old unprefixed `?search=…` will keep their page but lose their search term. Update shared links or accept the one-time break.

## [1.29.19] - 2026-04-19

### Added
- **`setFilterData()` extended signature** — the method now accepts five optional runtime-override args beyond `$data`:
  - `?string $value` — column name used for each option's value
  - `?string $option` — column name used for each option's label
  - `?string $key` — DB column the engine filters on
  - `?string $condition` — SQL condition (`=`, `LIKE`, `whereIn`, …)
  - `?string $callback` — method name on the component (not a closure — raw closures can't round-trip through Livewire state). Resolved to `[$this, $method]` and wrapped as `\Closure::fromCallable()` at query time so the engine's strict `?\Closure` type hints still match.
  - Any arg left as `null` keeps the value the factory produced in `setFilter()`. Backwards compatible — existing `setFilterData($id, $data)` call-sites unchanged.
- **`setFilterDateBounds($id, ?min, ?max, ?condition, ?callback)`** — new method, same pattern, but targeted at `date` / `date_range` filters. Throws `MrCatzException::setFilterDateBoundsNonDate` on non-date targets.
- **`clearFilterOverride($id, ?array $keys = null)`** — remove previously-set overrides. `$keys = null` wipes all; pass a list like `['min', 'max']` to clear specific fields. Valid keys: `'key'`, `'condition'`, `'value'`, `'option'`, `'min'`, `'max'`, `'callback'`.
- **7 new public override arrays on `HasFilters`** that persist through Livewire roundtrips — `filterKeyOverrides`, `filterConditionOverrides`, `filterValueColOverrides`, `filterOptionColOverrides`, `filterMinDateOverrides`, `filterMaxDateOverrides`, `filterCallbackOverrides`. Necessary because `setFilter()` re-runs fresh every render, so runtime overrides would otherwise be lost.
- **`applyFilterOverrides()`** — protected method on `HasFilters`, called from `render()` right after `getDataFilter()`. Patches both `$dataFilters` and `$activeFilters` from the override maps so the engine sees the final effective filter config before building its query. Also zeros `allow_exclude` on any check filter whose callback override is active (the toggle is meaningless when a closure owns the WHERE clause — symmetric with `createCheckWithCallback()` rejecting `->allowExclude()` at factory time).
- **3 new exceptions** — `MrCatzException::setFilterDateBoundsNonDate`, `MrCatzException::filterCallbackMethodNotFound`, and `MrCatzException::invalidCheckMode` (carry-over refinement).
- **13 new tests** covering: override property persistence, `applyFilterOverrides` patching, engine-side key/condition swap, callback method-name resolution, `?\Closure` type-hint regression on check-filter callback path, `setFilterDateBounds` validation, `clearFilterOverride` semantics, `allow_exclude` auto-hide, and a URL-boot integration test.

### Fixed
- **URL-param boot silently dropping filter values when a driver filter's `onFilterChanged` called `resetFilter`**. Scenario: loading `?filter[category_source]=category&filter[category][]=1` would render the first page without the category filter applied; only a second Livewire roundtrip (any interaction) showed the correct data. Root cause: `bootFilters` Phase 2 (`onFilterChanged` fan-out) could trigger `resetFilter` → `findData`, caching a `MrCatzDataTables` built from pre-restore `activeFilters`; Phase 3 restored the URL value but left the engine cache stale. Fix: invalidate `$this->mrCatzDataTables` at the end of the URL-params block so `render()`'s `getData()` rebuilds against the final restored state plus any queued overrides.
- **Check filter popover not reflecting runtime `setFilterData` / `setFilterDateBounds` changes until a page refresh**. `wire:key` used to hash only the applied value + mode, so when a driver filter mutated `filterData` or picker bounds while the target had no active value, the hash stayed stable, Livewire preserved the DOM, and Alpine kept its old x-data config. Fix: `wire:key` now also hashes the option list, the effective value/option column names, the `allow_exclude` flag, and (for `date_range`) the min/max bounds. Draft edits inside the popover still keep the hash stable so the popover doesn't close mid-edit.
- **Include/Exclude toggle visible on check filter even when a callback override was active**. The toggle flips `whereIn` ↔ `whereNotIn` at the engine, but the engine never routes `exclude_mode` through callback invocations — so the toggle was purely cosmetic noise. Now auto-hides while `filterCallbackOverrides[$id]` is set and returns to its factory value once the override is cleared.

### Docs
- Filter docs page expanded with full `setFilterData` param table, a stale-value gotcha callout, a new `setFilterDateBounds` section, and `clearFilterOverride` usage.
- API reference page updated with the extended method signatures and 5 newly-surfaced state helpers (`change`, `changeDateRange`, `applyCheck`, `toggleCheck`, `setCheckMode`).
- Demo page ("Try these" section) now includes two bullets walking readers through the runtime-override demo filters ("Filter Category by" and "Release Window") added to `DemoProductTable`.

## [1.29.18] - 2026-04-18

### Added
- **Check filter (multi-checkbox)** — new widget type alongside `select` / `date` / `date_range`. Two factories on `MrCatzDataTableFilter`:
  - `createCheck($id, $label, $data, $value, $option, $key, $condition = 'whereIn', $show = true)` — standard whereIn / whereNotIn.
  - `createCheckWithCallback($id, $label, $data, $value, $option, $callback, $show = true)` — callback receives `($query, array $values)` for custom SQL (joins, whereHas, etc).
- **Chainable `->allowExclude()`** — adds an Include/Exclude mode toggle to the popover; engine flips `whereIn` ↔ `whereNotIn` atomically. Rejected on `createCheckWithCallback` (callback owns its own SQL).
- **Chainable `->allowSearchWhen(?int $count = 5)`** — shows an in-popover search box once option count exceeds the threshold. `null` disables search entirely. Default 5 is aligned with the list area's visual scroll break.
- **Popover UI** — fixed-width (20rem) popover teleported to `<body>` (mirrors the `date_range` pattern). Includes sticky search box with case-insensitive `<mark>` highlight (XSS-safe via per-chunk escaping), scrollable list (`max-h-[16rem]`), selected/total counter, and Select-all / Clear-selection shortcuts that respect the current search filter.
- **Draft → Apply commit flow** — checkbox toggles, Select-all, Clear-selection, and Include/Exclude mode changes mutate in-popover draft state only. Nothing hits the server until the user clicks **Apply** (one atomic commit via new `applyCheck($id, $values, $mode)` Livewire method, running `findData()` exactly once). ESC / outside-click discards the draft; **Reset filter** and the trigger's clear-x both clear applied state and close the popover.
- **Export conditions integration** — check filters render in the export banner with mode-aware formatting: `"Status: Active, Pending"` for include mode, `"Status: NOT (Archived, Draft)"` for exclude mode. `check_box` icon used for the check filter's row in the banner.
- **Scout / Meilisearch pushdown** — non-callback check filters translate natively to `key IN [...]` / `key NOT IN [...]` expressions via `maybePushCheckFilter()`. Callback variants fall back to SQL (matches date-filter convention). Empty selection is a no-op (nothing pushed, nothing filtered).
- **12 new lang keys** (EN + ID) — `filter_check_pick`, `filter_check_search`, `filter_check_no_match`, `filter_check_selected`, `filter_check_select_all`, `filter_check_clear`, `filter_check_apply`, `filter_check_reset`, `filter_check_mode_include`, `filter_check_mode_exclude`, `filter_check_not_prefix`, `filter_check_plus_more`.
- **3 new exceptions** — `invalidCheckCondition`, `allowExcludeOnCallback`, `invalidCheckMode`.
- **20 new tests** — 8 unit (factory signatures, allowExclude rejection on callback variant, allowSearchWhen defaults, invalid condition throws), 12 integration (engine whereIn / whereNotIn / callback / exclude-mode flip, Scout pushdown IN / NOT IN / callback fallback / always-mode throw / empty no-op).

### Changed
- **Popover positioning** — both `mrcatzDateRange` and `mrcatzCheckFilter` now measure real `offsetHeight` on the `$nextTick` refine pass instead of relying on a hardcoded approximation. The flip-above case no longer leaves a large visible gap when the actual popover is shorter than the estimate.
- **`HasFilters::filterValueIsSet`** — teaches the check now accepts list-style arrays (`[1, 2, 3]`) instead of only date-range shapes (`['from' => ..., 'to' => ...]`). Empty lists correctly register as "unset" so they don't sync to URL params.
- **Active filter schema** — `activeFilters[]` entries now include an `exclude_mode` boolean alongside `type` / `format`. `bootFilters` + `change` + `changeDateRange` updated to write the field uniformly so engine + export code can rely on it.

## [1.29.12] - 2026-04-16

### Added
- CSV icon (`description` key) in the default inline-SVG icon set (`mrcatz_icon_svg()`), and mapped to `document-text` / `fa-solid fa-file-csv` in the Heroicon and Font Awesome icon sets — so the export modal's new CSV card renders a matching glyph regardless of the configured `icon_set`.
- CSV-aware rendering in the export banner. `MrCatzExport` now accepts `setFormat()` and `setHasIndexCol()`; the export blade reuses the same HTML template for XLSX and CSV but pads rows with empty cells instead of using `colspan`, and (on CSV) shifts the title / meta banner one column to the right when the first column is an index/No column so that column doesn't get auto-sized to the title string's width.
- `export_banner_exported`, `export_banner_total`, `export_banner_rows` translation keys (EN / ID). Excel and PDF export banners now use `mrcatz_lang()` instead of the hard-coded Indonesian "Diekspor / Total / data" string.

### Changed
- Export modal format cards reordered to **PDF → CSV → Excel**, with PDF as the default selection (previously Excel-first).
- `styles()` on `MrCatzExport` (and the published `App\Exports\DatatableExport` stub) now early-returns for CSV. `mergeCells()` is a spreadsheet mutation, not pure styling — for CSV it collapsed the shifted title-banner cells into the empty anchor column, producing three empty rows in the output.

## [1.29.11] - 2026-04-16

### Added
- `description` icon hint in `config/mrcatz.php` under the Export Modal section, mapped to `fa-file-csv` — so projects using the FontAwesome fallback icon set get a matching CSV glyph in the export modal instead of a missing icon.

## [1.29.10] - 2026-04-16

### Added
- CSV as a third export format alongside Excel and PDF. The export modal now renders a three-column format picker (Excel / CSV / PDF); selecting CSV routes through Laravel Excel's `\Maatwebsite\Excel\Excel::CSV` writer and downloads a `.csv` file. No extra dependency — `maatwebsite/excel` already handles CSV out of the box. Requested in a user-filed issue.

## [1.29.9] - 2026-04-16

### Fixed
- `dateRange()` field was silently skipped inside sections flagged with `->asCard()`. The multi-card layout branch in `form-builder.blade.php` was missing the `date_range` case that the single-grid branch already had, so any form using card-grouped sections (including the standalone playground) rendered every other field but dropped the date range.
- `monthYear()` and `year()` `min` / `max` parameters are now enforced at runtime. `monthYear()` gains native `min` / `max` attributes on its `<input type="month">` plus a JS clamp; `year()` (`<input type="number">`) gains a JS clamp so values typed past the range snap back before Livewire syncs (native number inputs only validate on submit).

## [1.29.8] - 2026-04-16

### Fixed
- `min` / `max` on `time()` and `datetime-local()` pickers are only validated natively on form-submit; the picker itself still lets users scroll past the range. Input now clamps the value on `change` and re-dispatches `input` / `change` so Livewire syncs the clamped value, giving real-time enforcement to match the `date()` behavior.

## [1.29.7] - 2026-04-16

### Added
- `MrCatzFormField::date()` gains `minDate` / `maxDate`, `datetime()` gains `minDateTime` / `maxDateTime`, and `time()` gains `minTime` / `maxTime`. Values are emitted as native `min` / `max` attributes on the picker input so browsers clamp selection on both mouse and keyboard.

## [1.29.6] - 2026-04-15

### Added
- `MrCatzFormField::mapPicker()` gains a `forceTheme` parameter (`'light' | 'dark' | null`). When set, the Leaflet tile layer is pinned to that theme regardless of the host app's `<html data-theme>`. Null (default) preserves the existing behavior of following `data-theme`. Leaflet-only — Google Maps ignores this flag because its default rendering doesn't hot-swap styles.

## [1.29.5] - 2026-04-15

### Fixed
- `withColumnImage()` with a table-prefixed key (e.g. `'demo_products.image_url'`) rendered the fallback initial instead of the image because the callback looked up `$data->{'demo_products.image_url'}` on a row object whose property is just `image_url`. The callback now strips the table prefix the same way `getData()` does, and applies the same rule to the `fallback` key. `getExpandView()` image and text field readers get the same treatment so their `key` / `fallback` entries can be qualified too.

## [1.29.4] - 2026-04-15

### Fixed
- Inline-edit default value showed the page-1 row's value when editing on other pagination pages. Alpine's `x-data` only initializes `val` once and Livewire's morph preserves Alpine state across re-renders, so the input opened with stale data even though the Blade-rendered display was correct. The wrappers now carry a `data-current-value` attribute (which morphs cleanly), and the double-click / mobile click handlers reset `val` from it before opening the editor.

## [1.29.3] - 2026-04-15

### Added
- Validation errors for bulk action form-builder fields now render under the field. New `errorKey` field metadata (set to `bulkFormData.{id}` for bulk fields, falls back to `id` for the standard edit form) is consumed by every form partial via `@error($errorKey ?? $id)`.
- Mobile "More" dropdown in the bulk toolbar now uses DaisyUI v5's popover-API with CSS anchor positioning, so the menu auto-flips/clamps inside the viewport instead of overflowing off-screen on narrow widths.

### Fixed
- Bulk toolbar header **select-all checkbox** stayed visually checked after `clearSelection()` (e.g. clicking Cancel) because Livewire's morph only updates the `checked` HTML attribute, not the live DOM property. Now bound via Alpine `:checked="$wire.selectAll"` so it stays in sync across server-driven state changes.

## [1.29.2] - 2026-04-15

### Added
- Responsive bulk toolbar. On mobile the custom action buttons collapse into a "More" dropdown (chevron-down icon) so only Cancel + one primary button stay visible; desktop (sm+) keeps every action as its own button. Built-in bulk Delete (when `$showBulkDeleteAction = true`) is always the mobile primary; if disabled, the first custom action takes its place.
- Button text inside the bulk toolbar now truncates with `…` + native tooltip (`title` attribute) so very long labels never overflow the button.
- New `btn_more` translation key (`More` / `Lainnya`).
- New `MrCatzEvent::BULK_ACTION_DONE` event — page-side modal dispatches it after a successful submit, the table clears its selection via an `#[On]` listener on `HasCustomBulkActions`.

### Changed
- Selection persistence: clicking a custom bulk action no longer clears `$selectedRows` immediately. Rows stay selected while the modal is open, so cancelling doesn't force the user to re-pick. Selection is cleared only after a successful `processBulkActionData()` run.

## [1.29.1] - 2026-04-15

### Added
- `buttonColor` parameter on `MrCatzBulkAction::create()` — pick any DaisyUI theme color (`primary`, `secondary`, `accent`, `neutral`, `info`, `success`, `warning`, `error`, `ghost`) for the toolbar button outline and the modal's submit button. Defaults to `'primary'`. The modal's header icon + background tint follow the same color automatically.

### Fixed
- Custom bulk action modal now renders on the **Page** component instead of the Table component, so `setBulkForm($id)` and `processBulkActionData()` hooks resolve correctly (they're defined on the page). The initial `1.29.0` release mistakenly colocated the modal with the table component, which caused the modal to fall back to the Edit form's fields.
- Bulk action modal visibility — the old implementation relied on Alpine + browser events that raced with Livewire re-renders, leaving the backdrop visible but the modal body hidden. Reworked to use a native `<dialog open>` with DaisyUI `.modal.modal-open` classes so visibility is driven entirely by Livewire state (`$activeBulkActionId`).
- Form Builder blade (`form-builder.blade.php`) now accepts an optional `$formFields` include parameter via `isset()` check so callers (e.g. the bulk modal) can pass a pre-built, namespaced field set without clashing with the Edit form's `$this->getFormFields()`.

### Changed
- Split `HasCustomBulkActions` trait into two: `HasCustomBulkActions` on the Table component (toolbar buttons + dispatch), and `HasCustomBulkActionModal` on `MrCatzComponent` (modal state + form rendering + submit handling).
- New `MrCatzEvent::BULK_ACTION_OPEN` event carries action metadata + selectedRows from the table to the page when a bulk button is clicked.

## [1.29.0] - 2026-04-15

### Added
- **Custom bulk actions.** New `MrCatzBulkAction` class + `setBulkAction()` hook on the table component, paired with `setBulkForm($id)` and `processBulkActionData($id, $selectedRows, $bulkFormData)` hooks on the page component. Two modes supported:
  - `'confirmation'` — simple confirm dialog (e.g. "Delete Selected Data?").
  - `'form'` — opens a modal rendering either a Form Builder form (`setBulkForm()` returns `MrCatzFormField[]`, auto-bound to `$bulkFormData` and auto-validated) or a blade `@yield` escape hatch (`setBulkForm()` returns a section name, user wires `wire:model="bulkFormData.*"` manually).
  Buttons render in the existing bulk toolbar alongside the built-in delete button. Example:
  ```php
  public function setBulkAction(): array
  {
      return [
          MrCatzBulkAction::create('bulk_category', 'form', 'Update Selected Data', null, null, 'edit'),
          MrCatzBulkAction::create('bulk_delete', 'confirmation', 'Delete Selected Data?', null, 'Selected data will be permanently deleted.', 'delete'),
      ];
  }
  ```
- `$showBulkDeleteAction` property on the table component (default `true`) to hide the built-in bulk delete button when you want full control over bulk operations via custom actions.
- `$bulkFormData` array property on `MrCatzComponent` holding form values submitted through bulk form modals.
- `MrCatzEvent::BULK_ACTION` event constant for the new table→page handoff.

### Changed
- `form-builder.blade.php` now accepts an optional `$formFields` include variable, enabling callers to render a pre-built, differently-namespaced field set (used internally by the bulk action modal so fields bind to `bulkFormData.*`). Existing call sites continue to use `$this->getFormFields()` unchanged.

## [1.23.10] - 2026-04-05

### Fixed
- `HasExport::buildExportData()` no longer drops display-only custom columns from exports. The previous skip heuristic (`key === null && index === null && !editable`) was too broad: it treated every `withCustomColumn()` without a `$key` as an action column and excluded it from PDF/Excel output, which silently discarded important data like dynamic year columns, computed per-row values, and formatted display cells. Classification now relies on the explicit `type` tag introduced in v1.23.5 — only columns marked as `type: 'action'` (via `withActionColumn()` or `withCustomColumn(..., type: 'action')`) are skipped, alongside image columns. If you still have legacy `withCustomColumn('Actions', fn ($d, $i) => MrCatzDataTables::getActionView(...))` call sites without a `type` tag, migrate to `withActionColumn()` or add `type: 'action'` to keep the action buttons out of exports.

## [1.23.9] - 2026-04-05

### Added
- `$urlPrefix` parameter on `getImageView()` (default `null`, backward compatible). When supplied, the helper runs `$url` through `resolveImageUrl($url, $urlPrefix)` so callers can pass a bare DB value (e.g. `"avatar.jpg"`) and let the helper build the final URL — the same contract `withColumnImage()` already exposes. Leaving `$urlPrefix` null preserves the legacy behavior where `$url` is rendered as-is.

## [1.23.8] - 2026-04-05

### Added
- `$gravity` parameter on `withColumnImage()` and `getImageView()` (default `'center'`, preserving current behavior). Accepts `'left'`, `'center'`, `'right'` and controls the horizontal alignment of the image within its table cell. `datatable-image.blade.php` maps it to `justify-start` / `justify-center` / `justify-end` on the outer flex wrapper. Useful when a table cell needs the thumbnail flush against the left edge instead of centered.

## [1.23.7] - 2026-04-05

### Removed
- Reverts the `enableAutoExpand()` helper and the automatic `setData()` fallback added in `v1.23.6`. The auto-fallback silently wired up expand content whenever `$expandableRows` was set, which felt too magical for callers who prefer to drive expand content explicitly via `enableExpand()`. The feature will return as an explicit opt-in if the need comes up again.

## [1.23.6] - 2026-04-05

### Added
- `MrCatzDataTables::enableAutoExpand()` — builds an expand view automatically from every plain `withColumn()` (columns with a real `$key` and no `type`). Skips index, image, action and custom callback columns. Useful when you want the mobile "more details" drawer without manually listing fields. **Reverted in v1.23.7.**
- Automatic fallback in `MrCatzDataTablesComponent::setData()`: if a component sets `$expandableRows` to `'mobile'`/`'desktop'`/`'both'` but never calls `enableExpand()` in `setTable()`, `enableAutoExpand()` is invoked for you. Tables get a free expand drawer by flipping a single property. **Reverted in v1.23.7.**

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
