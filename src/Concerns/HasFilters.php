<?php

namespace MrCatz\DataTable\Concerns;

use MrCatz\DataTable\Exceptions\MrCatzException;
use MrCatz\DataTable\MrCatzEvent;

trait HasFilters
{
    public $dataFilters = [];
    public $activeFilters = [];
    public $filterShow = [];
    public $filterData = [];

    /**
     * Runtime overrides applied on top of the filter definition returned by
     * setFilter(). Necessary because setFilter() re-runs fresh on every
     * render, so `setFilterData()` / `setFilterDateBounds()` changes would
     * otherwise be lost across Livewire roundtrips.
     *
     * Keyed by filter id. Any unset key falls back to the value the factory
     * produced in setFilter(). applyFilterOverrides() patches `dataFilters`
     * + `activeFilters` from these maps at the start of render().
     *
     * Callback overrides are stored as a **method name string** (serializable)
     * and resolved to a callable via `[$this, $methodName]` at query time.
     * Raw closures can't round-trip through Livewire state.
     */
    public array $filterKeyOverrides = [];
    public array $filterConditionOverrides = [];
    public array $filterValueColOverrides = [];
    public array $filterOptionColOverrides = [];
    public array $filterMinDateOverrides = [];
    public array $filterMaxDateOverrides = [];
    public array $filterCallbackOverrides = [];

    public $default_filter_value = '';
    private array $filterChangeStack = [];

    protected function bootFilters(): void
    {
        foreach ($this->setFilter() as $f => $filter) {
            $this->filterShow[$f] = $filter->getDataFilter()['show'];
            $this->filterData[$f] = $filter->getDataFilter()['data'];
        }

        if (!empty($this->filterUrlParams)) {
            // Snapshot URL params before any mutation by onFilterChanged/resetFilter
            $savedUrlParams = $this->filterUrlParams;

            foreach ($savedUrlParams as $id => $value) {
                $config = $this->findFilterConfigById($id);
                if ($config) {
                    $this->activeFilters[] = [
                        'id'           => $id,
                        'key'          => $config['key'],
                        'value'        => $value,
                        'condition'    => $config['condition'],
                        'type'         => $config['type']   ?? 'select',
                        'format'       => $config['format'] ?? '',
                        'exclude_mode' => false,
                    ];
                }
            }

            // Trigger onFilterChanged so dependent filters are initialized
            // (e.g. show/hide, load dropdown data). Use strict null/'' check
            // so that legitimate falsy values like 0, '0', false are kept.
            foreach ($savedUrlParams as $id => $value) {
                if (self::filterValueIsSet($value)) {
                    $this->onFilterChanged($id, $value);
                }
            }

            // Restore values that were cleared by resetFilter() inside onFilterChanged()
            foreach ($savedUrlParams as $id => $value) {
                if (self::filterValueIsSet($value)) {
                    foreach ($this->activeFilters as $i => $af) {
                        if ($af['id'] === $id) {
                            $this->activeFilters[$i]['value'] = $value;
                            break;
                        }
                    }
                }
            }

            // Re-sync URL params so they match the restored activeFilters
            $this->syncFilterUrl();

            // Any resetFilter() / change() inside Phase 2's onFilterChanged chain
            // will have called $this->findData(), which caches a MrCatzDataTables
            // instance built from PRE-restore activeFilters. Null the cache here
            // so render()'s getData() rebuilds with the final restored state +
            // any runtime overrides queued up by setFilterData / setFilterDateBounds.
            // Without this, URL-driven filter values get silently dropped on the
            // very first render after mount — noticeable when the user lands on
            // a deep-linked URL and the filter visibly "does nothing" until a
            // second Livewire roundtrip.
            if (property_exists($this, 'mrCatzDataTables')) {
                $this->mrCatzDataTables = null;
            }
        }
    }

    // Override-able — no strict types for backward compatibility
    public function setFilter() { return []; }

    public function setFilterShow(string $id, bool $show): void
    {
        foreach ($this->setFilter() as $f => $filter) {
            if ($filter->getDataFilter()['id'] == $id) {
                $this->filterShow[$f] = $show;
                return;
            }
        }
        throw MrCatzException::filterNotFound($id);
    }

    public function resetFilter(string $id): void
    {
        $found = false;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $this->activeFilters[$i]['value'] = null;
                $found = true;
                break;
            }
        }
        if (!$found) {
            // Filter may not be active yet — not an error, just no-op
        }
        $this->syncFilterUrl();
        $this->findData();
    }

    /**
     * Replace the option list for a filter. All non-data args are optional
     * runtime overrides for the filter's definition — any arg left as null
     * keeps the value the factory produced in `setFilter()`.
     *
     * Typical use case: point a select/check filter at a different underlying
     * table whose column shape differs. Example:
     *
     *     // Was: categories table with id+name → products.category_id
     *     // Now: tags table with slug+label → products.tag_id, as whereIn
     *     $this->setFilterData(
     *         'category',
     *         DB::table('tags')->get(),
     *         value: 'slug',
     *         option: 'label',
     *         key: 'products.tag_id',
     *         condition: 'whereIn',
     *     );
     *
     * Callback overrides accept a **method name string** on this component
     * (resolved via [$this, $methodName] at query time) — raw closures aren't
     * serializable across Livewire requests.
     *
     * Does NOT auto-reset the active value when key/value/option change. If
     * the previously-picked value becomes invalid under the new column
     * (e.g. an old category_id that doesn't exist in the new tag_id column),
     * call `resetFilter($id)` explicitly after setFilterData().
     */
    public function setFilterData(
        string $id,
        array|object $data,
        ?string $value = null,
        ?string $option = null,
        ?string $key = null,
        ?string $condition = null,
        ?string $callback = null,
    ): void {
        $found = false;
        foreach ($this->setFilter() as $f => $filter) {
            if ($filter->getDataFilter()['id'] == $id) {
                $this->filterData[$f] = is_array($data) ? $data : json_decode(json_encode($data), true);
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw MrCatzException::filterNotFound($id);
        }

        if ($value     !== null) $this->filterValueColOverrides[$id]  = $value;
        if ($option    !== null) $this->filterOptionColOverrides[$id] = $option;
        if ($key       !== null) $this->filterKeyOverrides[$id]       = $key;
        if ($condition !== null) $this->filterConditionOverrides[$id] = $condition;
        if ($callback  !== null) $this->filterCallbackOverrides[$id]  = $callback;
    }

    /**
     * Override min/max bounds (and optionally condition/callback) for a
     * `date` or `date_range` filter at runtime. Useful when the bounds
     * should react to another filter's value — e.g. a "Release window"
     * select that narrows the acceptable range for `release_on`.
     *
     * Throws if the filter isn't of date / date_range type. Null args are
     * skipped — each call mutates only the supplied fields. Callbacks are
     * stored as method-name strings (see setFilterData for reasoning).
     */
    public function setFilterDateBounds(
        string $id,
        ?string $min = null,
        ?string $max = null,
        ?string $condition = null,
        ?string $callback = null,
    ): void {
        $found = false;
        $type  = null;
        foreach ($this->setFilter() as $filter) {
            $df = $filter->getDataFilter();
            if ($df['id'] == $id) {
                $type  = $df['type'] ?? 'select';
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw MrCatzException::filterNotFound($id);
        }
        if (!in_array($type, ['date', 'date_range'], true)) {
            throw MrCatzException::setFilterDateBoundsNonDate($id, $type);
        }

        if ($min       !== null) $this->filterMinDateOverrides[$id]   = $min;
        if ($max       !== null) $this->filterMaxDateOverrides[$id]   = $max;
        if ($condition !== null) $this->filterConditionOverrides[$id] = $condition;
        if ($callback  !== null) $this->filterCallbackOverrides[$id]  = $callback;
    }

    /**
     * Clear previously-set overrides for a filter. Pass `$keys = null` to
     * clear all overrides for this filter, or a list like
     * `['key', 'condition']` to clear only specific ones.
     */
    public function clearFilterOverride(string $id, ?array $keys = null): void
    {
        $maps = [
            'key'       => 'filterKeyOverrides',
            'condition' => 'filterConditionOverrides',
            'value'     => 'filterValueColOverrides',
            'option'    => 'filterOptionColOverrides',
            'min'       => 'filterMinDateOverrides',
            'max'       => 'filterMaxDateOverrides',
            'callback'  => 'filterCallbackOverrides',
        ];

        $targets = $keys === null ? array_keys($maps) : $keys;
        foreach ($targets as $t) {
            if (isset($maps[$t])) {
                $prop = $maps[$t];
                unset($this->{$prop}[$id]);
            }
        }
    }

    /**
     * Patch `dataFilters` + `activeFilters` from the per-id override maps.
     * Invoked once at the start of each render so Livewire-persisted overrides
     * survive the setFilter() rebuild.
     */
    protected function applyFilterOverrides(): void
    {
        $patch = function (array &$entry, string $id) {
            if (isset($this->filterKeyOverrides[$id]))       $entry['key']       = $this->filterKeyOverrides[$id];
            if (isset($this->filterConditionOverrides[$id])) $entry['condition'] = $this->filterConditionOverrides[$id];
            if (isset($this->filterValueColOverrides[$id]))  $entry['value']     = $this->filterValueColOverrides[$id];
            if (isset($this->filterOptionColOverrides[$id])) $entry['option']    = $this->filterOptionColOverrides[$id];
            if (isset($this->filterMinDateOverrides[$id]))   $entry['min_date']  = $this->filterMinDateOverrides[$id];
            if (isset($this->filterMaxDateOverrides[$id]))   $entry['max_date']  = $this->filterMaxDateOverrides[$id];
        };

        foreach ($this->dataFilters as $i => $df) {
            $patch($this->dataFilters[$i], $df['id']);

            // When a callback override is active, the check filter's
            // Include/Exclude toggle is meaningless — the callback owns
            // the WHERE clause and the engine doesn't apply `exclude_mode`
            // through whereIn/whereNotIn. Same rationale that makes
            // createCheckWithCallback() reject ->allowExclude() at
            // factory time: applies symmetrically at runtime.
            if (isset($this->filterCallbackOverrides[$df['id']])) {
                $this->dataFilters[$i]['allow_exclude'] = false;
            }
        }

        // activeFilters snapshots `key`/`condition` at change() time — we need
        // to repatch them on each render so the engine uses the latest override.
        // The `value` / `option` column names aren't stored on activeFilters
        // (they're display-only), so no patching needed for those.
        foreach ($this->activeFilters as $i => $af) {
            $id = $af['id'];
            if (isset($this->filterKeyOverrides[$id]))       $this->activeFilters[$i]['key']       = $this->filterKeyOverrides[$id];
            if (isset($this->filterConditionOverrides[$id])) $this->activeFilters[$i]['condition'] = $this->filterConditionOverrides[$id];
        }
    }

    public function change(string $id, mixed $value): void
    {
        $filter = $this->findFilterById($id);
        $filterValue = $value === '' ? null : $value;

        // For single date filters, clamp to min/max if configured
        if (($filter['type'] ?? 'select') === 'date') {
            $filterValue = $this->clampDateValue($filter, $filterValue);
        }

        $found = false;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $this->activeFilters[$i]['value'] = $filterValue;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->activeFilters[] = [
                'id'           => $id,
                'key'          => $filter['key'],
                'value'        => $filterValue,
                'condition'    => $filter['condition'],
                'type'         => $filter['type']   ?? 'select',
                'format'       => $filter['format'] ?? '',
                'exclude_mode' => false,
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1, $this->setPageName());
        $this->clearSelection();
        $this->findData();
        $this->safeFilterChanged($id, $filterValue);
    }

    /**
     * Toggle a single value into/out of a `check` filter's selected array.
     * Immediately re-queries the data — matches the UX of native checkbox
     * lists where each tick applies.
     */
    public function toggleCheck(string $id, mixed $value): void
    {
        $filter = $this->findFilterById($id);

        $activeIndex = null;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $activeIndex = $i;
                break;
            }
        }

        $current = [];
        if ($activeIndex !== null && is_array($this->activeFilters[$activeIndex]['value'] ?? null)) {
            $current = array_values($this->activeFilters[$activeIndex]['value']);
        }

        // Loose comparison: URL strings like '1' should match int 1 coming from DB.
        $pos = null;
        foreach ($current as $i => $v) {
            if ($v == $value) { $pos = $i; break; }
        }

        if ($pos === null) {
            $current[] = $value;
        } else {
            array_splice($current, $pos, 1);
        }

        $this->writeCheckValue($id, $filter, array_values($current));
    }

    /**
     * Replace a `check` filter's selected array wholesale — used by the UI's
     * Select-all / Clear-selection shortcuts and by external callers that
     * need to set multiple values atomically.
     */
    public function setCheckValues(string $id, array $values): void
    {
        $filter = $this->findFilterById($id);
        $this->writeCheckValue($id, $filter, array_values($values));
    }

    /**
     * Atomically commit both values AND mode for a `check` filter in a
     * single Livewire request — used by the popover's Apply button so the
     * engine runs only one findData() cycle per commit. Pass `$mode = null`
     * to leave the current exclude_mode untouched.
     */
    public function applyCheck(string $id, array $values, ?string $mode = null): void
    {
        if ($mode !== null && !in_array($mode, ['include', 'exclude'], true)) {
            throw MrCatzException::invalidCheckMode($mode);
        }

        $filter = $this->findFilterById($id);
        $values = array_values($values);

        $activeIndex = null;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $activeIndex = $i;
                break;
            }
        }

        if ($activeIndex !== null) {
            $this->activeFilters[$activeIndex]['value'] = $values;
            if ($mode !== null) {
                $this->activeFilters[$activeIndex]['exclude_mode'] = $mode === 'exclude';
            }
        } else {
            $this->activeFilters[] = [
                'id'           => $id,
                'key'          => $filter['key'],
                'value'        => $values,
                'condition'    => $filter['condition'],
                'type'         => $filter['type']   ?? 'check',
                'format'       => $filter['format'] ?? '',
                'exclude_mode' => $mode === 'exclude',
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1, $this->setPageName());
        $this->clearSelection();
        $this->findData();
        $this->safeFilterChanged($id, $values);
    }

    /**
     * Switch a `check` filter between 'include' (whereIn) and 'exclude'
     * (whereNotIn) mode. Only meaningful when the filter was built with
     * `->allowExclude()` — but we don't enforce that here because the
     * registry-level check happens at filter-config time.
     */
    public function setCheckMode(string $id, string $mode): void
    {
        if (!in_array($mode, ['include', 'exclude'], true)) {
            throw MrCatzException::invalidCheckMode($mode);
        }

        $filter = $this->findFilterById($id);

        $activeIndex = null;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $activeIndex = $i;
                break;
            }
        }

        $exclude = $mode === 'exclude';
        $current = $activeIndex !== null
            ? array_values($this->activeFilters[$activeIndex]['value'] ?? [])
            : [];

        if ($activeIndex !== null) {
            $this->activeFilters[$activeIndex]['exclude_mode'] = $exclude;
        } else {
            $this->activeFilters[] = [
                'id'           => $id,
                'key'          => $filter['key'],
                'value'        => $current,
                'condition'    => $filter['condition'],
                'type'         => $filter['type']   ?? 'check',
                'format'       => $filter['format'] ?? '',
                'exclude_mode' => $exclude,
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1, $this->setPageName());
        $this->clearSelection();
        $this->findData();
        $this->safeFilterChanged($id, $current);
    }

    private function writeCheckValue(string $id, array $filter, array $values): void
    {
        $activeIndex = null;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $activeIndex = $i;
                break;
            }
        }

        if ($activeIndex !== null) {
            $this->activeFilters[$activeIndex]['value'] = $values;
        } else {
            $this->activeFilters[] = [
                'id'           => $id,
                'key'          => $filter['key'],
                'value'        => $values,
                'condition'    => $filter['condition'],
                'type'         => $filter['type']   ?? 'check',
                'format'       => $filter['format'] ?? '',
                'exclude_mode' => false,
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1, $this->setPageName());
        $this->clearSelection();
        $this->findData();
        $this->safeFilterChanged($id, $values);
    }

    /**
     * Update one half (`from` or `to`) of a date_range filter. Auto-swaps if
     * the resulting `to` is earlier than `from`, and clamps each value to
     * min_date / max_date if configured.
     */
    public function changeDateRange(string $id, string $part, mixed $value): void
    {
        if (!in_array($part, ['from', 'to'], true)) {
            throw MrCatzException::invalidDateRangePart($part);
        }

        $filter = $this->findFilterById($id);
        $newValue = $value === '' ? null : $value;
        $newValue = $this->clampDateValue($filter, $newValue);

        // Find existing active entry (if any) so we can update one half
        $activeIndex = null;
        foreach ($this->activeFilters as $i => $af) {
            if ($af['id'] === $id) {
                $activeIndex = $i;
                break;
            }
        }

        $current = ['from' => null, 'to' => null];
        if ($activeIndex !== null && is_array($this->activeFilters[$activeIndex]['value'] ?? null)) {
            $current = array_merge($current, $this->activeFilters[$activeIndex]['value']);
        }

        $current[$part] = $newValue;

        // Auto-swap when both sides are set and out of order
        if (!empty($current['from']) && !empty($current['to']) && $current['from'] > $current['to']) {
            [$current['from'], $current['to']] = [$current['to'], $current['from']];
        }

        if ($activeIndex !== null) {
            $this->activeFilters[$activeIndex]['value'] = $current;
        } else {
            $this->activeFilters[] = [
                'id'           => $id,
                'key'          => $filter['key'],
                'value'        => $current,
                'condition'    => $filter['condition'],
                'type'         => $filter['type']   ?? 'date_range',
                'format'       => $filter['format'] ?? 'date',
                'exclude_mode' => false,
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1, $this->setPageName());
        $this->clearSelection();
        $this->findData();
        $this->safeFilterChanged($id, $current);
    }

    /**
     * Clamp a date value to the filter's min_date / max_date constraints,
     * if any. Lexicographic string comparison works for ISO 8601 formats
     * (YYYY-MM-DD, YYYY-MM-DDTHH:MM, HH:MM, YYYY-MM, YYYY).
     */
    private function clampDateValue(array $filter, mixed $value): mixed
    {
        if ($value === null || $value === '') return $value;

        $min = $filter['min_date'] ?? null;
        $max = $filter['max_date'] ?? null;

        if ($min !== null && $value < $min) return $min;
        if ($max !== null && $value > $max) return $max;

        return $value;
    }

    private function safeFilterChanged(string $id, mixed $value): void
    {
        if (in_array($id, $this->filterChangeStack)) {
            $this->filterChangeStack = [];
            return; // Circular dependency detected — break the loop
        }
        $this->filterChangeStack[] = $id;
        $this->onFilterChanged($id, $value);
        array_pop($this->filterChangeStack);
    }

    public function onFilterChanged($id, $value) {}

    private function findFilterConfigById(string $id): ?array
    {
        foreach ($this->setFilter() as $filter) {
            $df = $filter->getDataFilter();
            if ($df['id'] == $id) return $df;
        }
        return null;
    }

    private function syncFilterUrl(): void
    {
        $this->filterUrlParams = [];
        foreach ($this->activeFilters as $af) {
            if (self::filterValueIsSet($af['value'] ?? null)) {
                $this->filterUrlParams[$af['id']] = $af['value'];
            }
        }
    }

    /**
     * Check whether a filter value should be considered "set" — i.e. it
     * actually filters something. Distinguishes "no filter" (null / empty
     * string / empty range) from legitimate falsy values like 0, '0', false.
     *
     * Use this instead of `!empty(...)` everywhere a filter value is checked.
     */
    private static function filterValueIsSet(mixed $value): bool
    {
        if ($value === null || $value === '') return false;

        if (is_array($value)) {
            if (empty($value)) return false;

            // Date range — assoc with 'from' / 'to' keys.
            if (array_key_exists('from', $value) || array_key_exists('to', $value)) {
                return self::filterValueIsSet($value['from'] ?? null)
                    || self::filterValueIsSet($value['to']   ?? null);
            }

            // List array (check filter) — any non-empty list counts as set.
            return true;
        }

        return true;
    }

    private function getDataFilter(): array
    {
        $df = [];
        foreach ($this->setFilter() as $filter) {
            array_push($df, $filter->getDataFilter());
        }
        return $df;
    }

    private function findFilterById(string $id): array
    {
        foreach ($this->dataFilters as $filter) {
            if ($filter['id'] == $id) return $filter;
        }
        throw MrCatzException::filterNotFound($id);
    }

    private function findFilterCallbackById(string $id): ?callable
    {
        // Runtime override wins — resolves a method-name string to a callable
        // bound to this component. This is how `setFilterData()` /
        // `setFilterDateBounds()` let callers swap the filter's query closure
        // at runtime despite Livewire not being able to serialize raw closures.
        if (isset($this->filterCallbackOverrides[$id])) {
            $method = $this->filterCallbackOverrides[$id];
            if (!method_exists($this, $method)) {
                throw MrCatzException::filterCallbackMethodNotFound($id, $method);
            }
            // Engine methods type-hint ?Closure, not ?callable — [$this, $m] is
            // a callable but not a Closure. Wrap so the contract stays uniform.
            return \Closure::fromCallable([$this, $method]);
        }

        foreach ($this->setFilter() as $filter) {
            if ($filter->getDataFilter()['id'] == $id) return $filter->getCallback();
        }
        return null;
    }

    private function buildKeyValue(): array { return array_values($this->activeFilters); }

    private function buildFilterCallbacks(): array
    {
        $callbacks = [];
        foreach ($this->activeFilters as $filter) {
            $callbacks[] = $this->findFilterCallbackById($filter['id']);
        }
        return $callbacks;
    }
}
