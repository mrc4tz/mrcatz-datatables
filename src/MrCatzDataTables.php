<?php

namespace MrCatz\DataTable;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MrCatz\DataTable\Exceptions\MrCatzException;

class MrCatzDataTables
{
    private int $index = 0;
    public array $dataTableSet = [];
    private mixed $data = [];
    private array $callbacks = [];
    private string $search = '';
    private array $keyValue = [];
    private array $callbackFilters = [];
    private int $paginate = 5;
    private string $defaultOrderBy = 'created_at';
    private string $defaultOrderDirection = 'desc';
    private array $order = [];
    public EloquentBuilder|QueryBuilder|array $dataBuilder;
    private EloquentBuilder|QueryBuilder|array $baseDataBuilder;
    public array $paginateOptions = [5, 10, 15, 20];
    public bool $usePagination = true;
    private ?array $tables = null;
    private string $tableFindName = "table_find_765678912";
    private string $pageName = 'page';
    private ?\Closure $onDataLoaded = null;
    private int $currentPage = 1;
    private ?\Closure $bulkCallback = null;
    private ?\Closure $expandCallback = null;
    private ?\Closure $editableCallback = null;
    private array $pluckCache = [];
    private ?int $maxRows = null;
    private bool $hasMoreRows = false;
    public bool $hasEditAction = false;
    public bool $hasDeleteAction = false;

    /** @var int[]|null Ordered IDs returned by Scout, used to preserve relevance order in SQL. */
    private ?array $scoutOrderedIds = null;

    public static function with(
        EloquentBuilder|QueryBuilder|array $data,
        array $paginateOptions = [5, 10, 15, 20],
        ?int $paginate = null,
        bool $usePagination = true,
        string $pageName = 'page',
        ?callable $onDataLoaded = null
    ): self {
        $dt = new self();
        $dt->setBaseDataBuilder($data);
        $dt->paginateOptions = $paginateOptions;
        $dt->paginate = $paginate ?? $paginateOptions[0];
        $dt->usePagination = $usePagination;
        $dt->pageName = $pageName;
        $dt->onDataLoaded = $onDataLoaded ? \Closure::fromCallable($onDataLoaded) : null;
        return $dt;
    }

    public function setDefaultOrder(string $defaultOrderBy, string $defaultOrderDirection): self
    {
        $this->defaultOrderBy = $defaultOrderBy;
        $this->defaultOrderDirection = $defaultOrderDirection;
        return $this;
    }

    public function getPageName(): string { return $this->pageName; }

    public function addOrderBy(string $orderBy, string $orderDirection): self
    {
        $this->order[] = ['orderBy' => $orderBy, 'direction' => $orderDirection];
        return $this;
    }

    public function getPaginate(): int { return $this->paginate; }
    public function setBaseDataBuilder(mixed $baseDataBuilder): void { $this->baseDataBuilder = $baseDataBuilder; }
    public function getBaseDataBuilder(): mixed { return $this->baseDataBuilder; }

    public function setCurrentPage(int $page): self
    {
        $this->currentPage = $page;
        return $this;
    }

    public function setOrderByKey(string $key, string $order): self
    {
        for ($i = 0; $i < count($this->dataTableSet); $i++) {
            $this->dataTableSet[$i]['order'] = ($this->dataTableSet[$i]['key'] == $key) ? $order : null;
        }
        return $this;
    }

    public function setMultiSort(array $multiSort): self
    {
        for ($i = 0; $i < count($this->dataTableSet); $i++) {
            $this->dataTableSet[$i]['order'] = null;
            foreach ($multiSort as $s) {
                if ($this->dataTableSet[$i]['key'] == $s['key']) {
                    $this->dataTableSet[$i]['order'] = $s['dir'];
                }
            }
        }
        return $this;
    }

    public function setSearch(string $search): self { $this->search = $search; return $this; }

    public function setFilters(array $keyValue, array $callbackFilters): self
    {
        $this->keyValue = $keyValue;
        $this->callbackFilters = $callbackFilters;
        return $this;
    }

    public function setConfig(?array $tables): self
    {
        if ($tables != null) $this->tables = $tables;
        return $this;
    }

    public function setPaginate(int $perPage): self { $this->paginate = $perPage; return $this; }
    public function setMaxRows(?int $maxRows): self { $this->maxRows = $maxRows; return $this; }
    public function hasMoreRows(): bool { return $this->hasMoreRows; }
    public function hasData(): bool { return count($this->data) > 0; }

    public function build(): self
    {
        $this->pluckCache = [];
        $this->dataBuilder = clone $this->getBaseDataBuilder();
        $this->scoutOrderedIds = null;

        if ($this->shouldUseScout() && $this->search !== '') {
            // Scout flow: push pushable filters to Meilisearch, run Scout
            // search, then apply remaining (un-pushed) filters in SQL.
            $this->applyScoutSearch();
        } else {
            // Database flow (also used for scout driver when search is empty)
            $this->applyFilters();
            $this->applySearch();
        }

        $this->applyOrdering();
        $this->notifyDataLoaded();
        $this->executeQuery();
        return $this;
    }

    private function applyFilters(?array $keyValue = null, ?array $callbackFilters = null): void
    {
        $kvList = $keyValue       ?? $this->keyValue;
        $cbList = $callbackFilters ?? $this->callbackFilters;

        foreach ($kvList as $x => $kv) {
            $type        = $kv['type'] ?? 'select';
            $hasCallback = Arr::exists($cbList, $x) && $cbList[$x] != null;

            if ($type === 'date_range') {
                $this->applyDateRangeFilter($kv, $hasCallback ? $cbList[$x] : null);
                continue;
            }

            if ($type === 'date') {
                $this->applyDateFilter($kv, $hasCallback ? $cbList[$x] : null);
                continue;
            }

            // Default: legacy select-style filter.
            // Use strict null/'' check so legitimate falsy values like 0, '0',
            // false (e.g. ?filter[active]=0) are still applied.
            $hasValue = ($kv['value'] ?? null) !== null && ($kv['value'] ?? null) !== '';

            if ($hasValue) {
                if ($hasCallback) {
                    $this->dataBuilder = $cbList[$x]($this->dataBuilder, $kv['value']);
                } else {
                    if ($kv['key'] != '-') {
                        $this->dataBuilder = $this->dataBuilder->where($kv['key'], $kv['condition'], $kv['value']);
                    }
                }
            } else {
                if ($hasCallback) {
                    $this->dataBuilder = $cbList[$x]($this->dataBuilder, $kv['value']);
                }
            }
        }
    }

    /**
     * Apply a single-value date filter. Uses Laravel's portable date helpers
     * (whereDate / whereYear / whereMonth / whereTime) so the SQL works
     * across MySQL, PostgreSQL, and SQLite.
     */
    private function applyDateFilter(array $kv, ?\Closure $callback): void
    {
        $value = $kv['value'] ?? null;

        if (empty($value)) {
            // Empty value: only run callback if it explicitly handles empty input
            if ($callback !== null) {
                $this->dataBuilder = $callback($this->dataBuilder, $value);
            }
            return;
        }

        if ($callback !== null) {
            $this->dataBuilder = $callback($this->dataBuilder, $value);
            return;
        }

        if (($kv['key'] ?? '-') === '-') return;

        $this->applyDateComparison($kv['key'], $kv['format'] ?? 'date', $kv['condition'] ?? '=', $value);
    }

    /**
     * Apply a date range filter. Open-ended ranges are supported:
     *   - both set     → applies both bounds
     *   - only `from`  → applies lower bound only
     *   - only `to`    → applies upper bound only
     *   - both empty   → no-op (callbacks still get notified with nulls)
     */
    private function applyDateRangeFilter(array $kv, ?\Closure $callback): void
    {
        $value = $kv['value'] ?? null;
        $from  = is_array($value) ? ($value['from'] ?? null) : null;
        $to    = is_array($value) ? ($value['to']   ?? null) : null;

        $hasFrom = !empty($from);
        $hasTo   = !empty($to);

        // Both empty: only callbacks that explicitly handle empty input run
        if (!$hasFrom && !$hasTo) {
            if ($callback !== null) {
                $this->dataBuilder = $callback($this->dataBuilder, ['from' => null, 'to' => null]);
            }
            return;
        }

        if ($callback !== null) {
            $this->dataBuilder = $callback($this->dataBuilder, ['from' => $from, 'to' => $to]);
            return;
        }

        if (($kv['key'] ?? '-') === '-') return;

        $key    = $kv['key'];
        $format = $kv['format'] ?? 'date';

        if ($hasFrom) $this->applyDateComparison($key, $format, '>=', $from);
        if ($hasTo)   $this->applyDateComparison($key, $format, '<=', $to);
    }

    /**
     * Apply a date-aware where clause using the portable Laravel helper that
     * matches the requested format. Mutates $this->dataBuilder.
     */
    private function applyDateComparison(string $key, string $format, string $operator, mixed $value): void
    {
        $this->dataBuilder = match ($format) {
            'date'             => $this->dataBuilder->whereDate($key, $operator, $value),
            'datetime'         => $this->dataBuilder->where($key, $operator, $value),
            'time', 'time_hm'  => $this->dataBuilder->whereTime($key, $operator, $value),
            'year'             => $this->dataBuilder->whereYear($key, $operator, $value),
            'month_year'       => $this->applyMonthYearComparison($key, $operator, $value),
            default            => $this->dataBuilder->where($key, $operator, $value),
        };
    }

    /**
     * Translate a `month_year` comparison (value format: 'YYYY-MM') into a
     * combination of whereYear/whereMonth that respects the requested operator.
     * Supports all 6 standard operators.
     */
    private function applyMonthYearComparison(string $key, string $operator, string $value): EloquentBuilder|QueryBuilder
    {
        $parts = explode('-', $value);
        if (count($parts) !== 2) return $this->dataBuilder;

        $year  = (int) $parts[0];
        $month = (int) $parts[1];

        if ($operator === '=') {
            return $this->dataBuilder
                ->whereYear($key, $year)
                ->whereMonth($key, $month);
        }

        if ($operator === '!=' || $operator === '<>') {
            return $this->dataBuilder->where(function ($q) use ($key, $year, $month) {
                $q->whereYear($key, '!=', $year)
                  ->orWhereMonth($key, '!=', $month);
            });
        }

        if (in_array($operator, ['>', '>='], true)) {
            $strict = $operator === '>';
            return $this->dataBuilder->where(function ($q) use ($key, $year, $month, $strict) {
                $q->whereYear($key, '>', $year)
                  ->orWhere(function ($q2) use ($key, $year, $month, $strict) {
                      $q2->whereYear($key, $year)
                         ->whereMonth($key, $strict ? '>' : '>=', $month);
                  });
            });
        }

        if (in_array($operator, ['<', '<='], true)) {
            $strict = $operator === '<';
            return $this->dataBuilder->where(function ($q) use ($key, $year, $month, $strict) {
                $q->whereYear($key, '<', $year)
                  ->orWhere(function ($q2) use ($key, $year, $month, $strict) {
                      $q2->whereYear($key, $year)
                         ->whereMonth($key, $strict ? '<' : '<=', $month);
                  });
            });
        }

        throw MrCatzException::dateOperatorNotSupported($operator, 'month_year');
    }

    private function applySearch(): void
    {
        if ($this->search == '') return;

        $searchableColumns = $this->getEffectiveSearchColumns();
        $explodedSearch = array_filter(explode(" ", $this->search), fn($s) => !empty($s));

        if (empty($searchableColumns) || count($explodedSearch) == 0) return;

        $typoConfig = $this->resolveTypoToleranceConfig();

        $this->dataBuilder = self::applySearchWhere($this->dataBuilder, $this->search, $searchableColumns, $typoConfig);

        if ($this->tables != null && isset($this->tables['table_name'], $this->tables['table_id'])) {
            $relevanceSub = $this->buildRelevanceSubquery($explodedSearch, $searchableColumns, $typoConfig);
            $findName = $this->tableFindName;
            $tableId = $this->tables['table_id'];
            $tableName = $this->tables['table_name'];

            $this->dataBuilder = $this->dataBuilder
                ->joinSub($relevanceSub, $findName, function ($join) use ($findName, $tableId, $tableName) {
                    $join->on("{$findName}.{$tableId}", '=', "{$tableName}.{$tableId}");
                });
        }
    }

    /**
     * Whether the configured search driver is 'scout'.
     */
    private function shouldUseScout(): bool
    {
        $driver = $this->tables['search']['driver'] ?? 'database';
        return $driver === 'scout';
    }

    /**
     * Run a Scout-powered search and apply the resulting IDs (in relevance
     * order) to the main query. Pushable filters are forwarded to Meilisearch;
     * un-pushable filters fall back to SQL.
     */
    private function applyScoutSearch(): void
    {
        $this->validateScoutDriver();

        $config       = $this->tables['search'];
        $proxyClass   = $this->resolveScoutModel();
        $maxResults   = (int) ($config['scout_max_results'] ?? 1000);

        // Determine which filters can be pushed to Meilisearch and which
        // must remain in SQL.
        [$filterString, $unpushedKv, $unpushedCb] = $this->prepareFilterPushdown();

        // Run Scout search
        $searchBuilder = $proxyClass::search($this->search);
        if ($filterString !== null) {
            $searchBuilder = $searchBuilder->options(['filter' => $filterString]);
        }

        $ids = $searchBuilder->take($maxResults)->keys()->all();

        $tableName = $this->tables['table_name'] ?? null;
        $tableId   = $this->tables['table_id']   ?? 'id';
        $idCol     = $tableName ? "{$tableName}.{$tableId}" : $tableId;

        if (empty($ids)) {
            // Force the main query to return nothing
            $this->dataBuilder = $this->dataBuilder->whereRaw('1 = 0');
        } else {
            $this->dataBuilder = $this->dataBuilder->whereIn($idCol, $ids);
            $this->scoutOrderedIds = $ids;
        }

        // Apply remaining filters in SQL (the ones we couldn't push)
        $this->applyFilters($unpushedKv, $unpushedCb);
    }

    /**
     * Validate that Scout is installed, the proxy model exists and uses the
     * Searchable trait, and that filter_pushdown (if provided) is a known mode.
     * filter_pushdown defaults to 'auto' when omitted.
     */
    private function validateScoutDriver(): void
    {
        $config = $this->tables['search'] ?? [];

        // Config-level checks first (cheaper + more actionable than env checks)
        if (isset($config['filter_pushdown']) && !in_array($config['filter_pushdown'], ['auto', 'always', 'never'], true)) {
            throw MrCatzException::invalidFilterPushdownMode($config['filter_pushdown']);
        }

        $base = $this->getBaseDataBuilder();
        if (is_array($base)) {
            throw MrCatzException::scoutRequiresBuilder();
        }

        // Env-level: Scout package must be installed
        if (!class_exists(\Laravel\Scout\Searchable::class)) {
            throw MrCatzException::scoutNotInstalled();
        }

        $proxyClass = $this->resolveScoutModel();

        if (!class_exists($proxyClass)) {
            throw MrCatzException::scoutModelNotFound($proxyClass);
        }

        if (!in_array(\Laravel\Scout\Searchable::class, class_uses_recursive($proxyClass))) {
            throw MrCatzException::modelNotSearchable($proxyClass);
        }
    }

    /**
     * Resolve which class to use for Scout. Order of preference:
     *  1. Explicit 'scout_model' in config
     *  2. Underlying model when baseQuery() returns an Eloquent Builder
     *  3. Throw — Query Builder users must provide a proxy model explicitly
     */
    private function resolveScoutModel(): string
    {
        $proxyClass = $this->tables['search']['scout_model'] ?? null;
        if ($proxyClass) return $proxyClass;

        $base = $this->getBaseDataBuilder();
        if ($base instanceof EloquentBuilder) {
            return get_class($base->getModel());
        }

        if ($base instanceof QueryBuilder) {
            throw MrCatzException::scoutModelRequiredForQueryBuilder();
        }

        throw MrCatzException::scoutRequiresBuilder();
    }

    /**
     * Walk the registered filters and decide which can be translated to a
     * Meilisearch filter expression. Returns:
     *   [filterString|null, unpushedKv[], unpushedCb[]]
     *
     * Behavior depends on filter_pushdown mode:
     *   - 'never'  → push nothing, all filters stay in SQL
     *   - 'auto'   → push what we can, warn (E_USER_WARNING) for the rest
     *   - 'always' → push everything; throw if any filter can't be translated
     */
    private function prepareFilterPushdown(): array
    {
        $mode = $this->tables['search']['filter_pushdown'] ?? 'auto';

        if ($mode === 'never') {
            return [null, $this->keyValue, $this->callbackFilters];
        }

        $pushed     = [];
        $unpushedKv = [];
        $unpushedCb = [];

        foreach ($this->keyValue as $x => $kv) {
            $hasCallback = Arr::exists($this->callbackFilters, $x) && $this->callbackFilters[$x] !== null;
            $type        = $kv['type'] ?? 'select';

            // Date / date_range filters: try to push if user configured a
            // 'date_field_map' entry pointing to a Meilisearch timestamp field.
            // Otherwise (or if format/condition can't be translated), fall back
            // to SQL with a clear warning.
            if ($type === 'date' || $type === 'date_range') {
                $expr = $this->maybePushDateFilter($kv, $hasCallback, $mode);
                if ($expr !== null) {
                    $pushed[] = $expr;
                    continue;
                }
                $unpushedKv[$x] = $kv;
                if ($hasCallback) {
                    $unpushedCb[$x] = $this->callbackFilters[$x];
                }
                continue;
            }

            // Empty filter values: nothing to push, but callbacks may still
            // need to run in SQL even with an empty value (existing semantics).
            if (empty($kv['value'])) {
                if ($hasCallback) {
                    $unpushedKv[$x] = $kv;
                    $unpushedCb[$x] = $this->callbackFilters[$x];
                }
                continue;
            }

            // Callback filters can never be pushed (they're closures)
            if ($hasCallback) {
                if ($mode === 'always') {
                    throw MrCatzException::filterNotPushable($kv['key'], 'uses a callback closure');
                }
                trigger_error(
                    "MrCatz: filter [{$kv['key']}] uses a callback and cannot be pushed to Meilisearch — falling back to SQL.",
                    E_USER_WARNING
                );
                $unpushedKv[$x] = $kv;
                $unpushedCb[$x] = $this->callbackFilters[$x];
                continue;
            }

            // Try to translate the simple filter
            $expr = $this->translateFilterToMeilisearch($kv);
            if ($expr === null) {
                $reason = "operator [{$kv['condition']}] is not translatable";
                if ($mode === 'always') {
                    throw MrCatzException::filterNotPushable($kv['key'], $reason);
                }
                trigger_error(
                    "MrCatz: filter [{$kv['key']}] {$reason} — falling back to SQL.",
                    E_USER_WARNING
                );
                $unpushedKv[$x] = $kv;
                continue;
            }

            $pushed[] = $expr;
        }

        $filterString = empty($pushed) ? null : implode(' AND ', $pushed);
        return [$filterString, $unpushedKv, $unpushedCb];
    }

    /**
     * Decide whether a date / date_range filter can be pushed to Meilisearch.
     * Returns the Meilisearch filter expression on success, or null when the
     * filter must fall back to SQL. Throws in 'always' mode when push fails.
     *
     * Push requires:
     *   - filter is NOT a callback variant (closures aren't translatable)
     *   - $tables['search']['date_field_map'] has an entry for this filter's key
     *   - the format is one Meilisearch can express via numeric range
     *     (date / datetime / year / month_year — but NOT time / time_hm)
     */
    private function maybePushDateFilter(array $kv, bool $hasCallback, string $mode): ?string
    {
        $key  = $kv['key']  ?? '-';
        $type = $kv['type'] ?? 'date';

        // Callback variants always go to SQL — no opportunity to push.
        if ($hasCallback) {
            if ($mode === 'always') {
                throw MrCatzException::filterNotPushable($key, "{$type} callback closure cannot be pushed");
            }
            return null;
        }

        $dateFieldMap = $this->tables['search']['date_field_map'] ?? [];
        $scoutField   = $dateFieldMap[$key] ?? null;

        if ($scoutField === null) {
            if ($mode === 'always') {
                throw MrCatzException::filterNotPushable(
                    $key,
                    "no date_field_map entry for key [{$key}] — add one to push, or use 'auto'/'never' mode"
                );
            }
            trigger_error(
                "MrCatz: {$type} filter [{$key}] has no date_field_map entry — falling back to SQL.",
                E_USER_WARNING
            );
            return null;
        }

        $tz = $this->tables['search']['date_timezone'] ?? config('app.timezone', 'UTC');
        $expr = $this->translateDateFilterToMeilisearch($kv, $scoutField, $tz);

        if ($expr === null) {
            $format = $kv['format'] ?? 'date';
            if ($mode === 'always') {
                throw MrCatzException::filterNotPushable(
                    $key,
                    "format [{$format}] cannot be translated to Meilisearch (use 'auto' or 'never' mode)"
                );
            }
            trigger_error(
                "MrCatz: {$type} filter [{$key}] format [{$format}] is not Meilisearch-translatable — falling back to SQL.",
                E_USER_WARNING
            );
            return null;
        }

        return $expr;
    }

    /**
     * Translate a date / date_range filter to a Meilisearch filter expression.
     * Returns null when the format cannot be expressed (time, time_hm) or when
     * the value is malformed.
     */
    private function translateDateFilterToMeilisearch(array $kv, string $scoutField, string $tz): ?string
    {
        $type   = $kv['type']   ?? 'date';
        $format = $kv['format'] ?? 'date';

        // time-of-day formats can't be expressed as a single numeric range
        if ($format === 'time' || $format === 'time_hm') return null;

        if ($type === 'date_range') {
            return $this->translateDateRangeForScout($kv['value'] ?? null, $scoutField, $format, $tz);
        }

        if ($type === 'date') {
            return $this->translateSingleDateForScout($kv, $scoutField, $format, $tz);
        }

        return null;
    }

    /**
     * Translate a single-value date filter (with operator) to a Meilisearch
     * range expression. format='date' '=' 2024-06-15 becomes a same-day range
     * because Meilisearch has no whereDate semantics.
     */
    private function translateSingleDateForScout(array $kv, string $field, string $format, string $tz): ?string
    {
        $value = $kv['value'] ?? null;
        if (empty($value)) return null;

        $condition = $kv['condition'] ?? '=';

        try {
            [$start, $end] = $this->periodBoundsToTimestamp((string) $value, $format, $tz);
        } catch (\Throwable $e) {
            return null;
        }

        return match ($condition) {
            '=', '=='  => "({$field} >= {$start} AND {$field} < {$end})",
            '!=', '<>' => "({$field} < {$start} OR {$field} >= {$end})",
            '>'        => "{$field} >= {$end}",
            '>='       => "{$field} >= {$start}",
            '<'        => "{$field} < {$start}",
            '<='       => "{$field} < {$end}",
            default    => null,
        };
    }

    /**
     * Translate a date_range filter to a Meilisearch expression. Open-ended
     * ranges supported (only `from` or only `to` set).
     */
    private function translateDateRangeForScout(mixed $value, string $field, string $format, string $tz): ?string
    {
        if (!is_array($value)) return null;

        $from = $value['from'] ?? null;
        $to   = $value['to']   ?? null;

        if (empty($from) && empty($to)) return null;

        $exprs = [];

        if (!empty($from)) {
            try {
                [$fromStart, ] = $this->periodBoundsToTimestamp((string) $from, $format, $tz);
                $exprs[] = "{$field} >= {$fromStart}";
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (!empty($to)) {
            try {
                [, $toEnd] = $this->periodBoundsToTimestamp((string) $to, $format, $tz);
                $exprs[] = "{$field} < {$toEnd}";
            } catch (\Throwable $e) {
                return null;
            }
        }

        return '(' . implode(' AND ', $exprs) . ')';
    }

    /**
     * Convert a date filter value to its [start, end) Unix timestamp bounds in
     * the user's configured timezone. The end is EXCLUSIVE — that's how a
     * "single day" / "single year" / "single month" maps to a numeric range
     * compatible with Meilisearch comparison operators.
     *
     * Carbon handles DST boundaries automatically.
     */
    private function periodBoundsToTimestamp(string $value, string $format, string $tz): array
    {
        switch ($format) {
            case 'date':
                $start = \Carbon\Carbon::parse($value, $tz)->startOfDay();
                return [
                    $start->copy()->utc()->timestamp,
                    $start->copy()->addDay()->utc()->timestamp,
                ];

            case 'datetime':
                // HTML datetime-local has minute precision; treat equality as
                // a 1-minute window for forgiving UX.
                $dt = \Carbon\Carbon::parse($value, $tz);
                return [
                    $dt->copy()->utc()->timestamp,
                    $dt->copy()->addMinute()->utc()->timestamp,
                ];

            case 'year':
                $year = (int) $value;
                return [
                    \Carbon\Carbon::create($year,     1, 1, 0, 0, 0, $tz)->utc()->timestamp,
                    \Carbon\Carbon::create($year + 1, 1, 1, 0, 0, 0, $tz)->utc()->timestamp,
                ];

            case 'month_year':
                $parts = explode('-', $value);
                if (count($parts) !== 2) {
                    throw new \InvalidArgumentException("Invalid month_year value: {$value}");
                }
                $start = \Carbon\Carbon::create((int) $parts[0], (int) $parts[1], 1, 0, 0, 0, $tz);
                return [
                    $start->copy()->utc()->timestamp,
                    $start->copy()->addMonth()->utc()->timestamp,
                ];

            default:
                throw new \InvalidArgumentException("Unsupported format for timestamp conversion: {$format}");
        }
    }

    /**
     * Translate a single MrCatz filter ['key', 'condition', 'value'] into a
     * Meilisearch filter expression, or null if it can't be translated.
     */
    private function translateFilterToMeilisearch(array $kv): ?string
    {
        $key       = $kv['key'];
        $condition = strtolower(trim((string) ($kv['condition'] ?? '=')));
        $value     = $kv['value'];

        if ($key === '-') return null;

        // Quote string values; numeric stays bare.
        $quote = function ($v) {
            if (is_numeric($v)) return (string) $v;
            return '"' . str_replace('"', '\\"', (string) $v) . '"';
        };

        return match ($condition) {
            '=', '=='  => "{$key} = " . $quote($value),
            '!=', '<>' => "{$key} != " . $quote($value),
            '>'        => "{$key} > " . $quote($value),
            '<'        => "{$key} < " . $quote($value),
            '>='       => "{$key} >= " . $quote($value),
            '<='       => "{$key} <= " . $quote($value),
            default    => null, // 'like', 'between', etc. are not translatable
        };
    }

    /**
     * Apply DB-specific ORDER BY to preserve the relevance order returned by
     * Scout. Called from applyOrdering() when scoutOrderedIds is set.
     */
    private function applyScoutOrderBy(): void
    {
        if (empty($this->scoutOrderedIds)) return;

        $base = $this->getBaseDataBuilder();
        $connection = $base instanceof EloquentBuilder
            ? $base->getQuery()->getConnection()
            : $base->getConnection();

        $driver    = $connection->getDriverName();
        $tableName = $this->tables['table_name'] ?? null;
        $tableId   = $this->tables['table_id']   ?? 'id';
        $idCol     = $tableName ? "{$tableName}.{$tableId}" : $tableId;
        $ids       = $this->scoutOrderedIds;
        $count     = count($ids);

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $placeholders = implode(',', array_fill(0, $count, '?'));
            $this->dataBuilder = $this->dataBuilder->orderByRaw("FIELD({$idCol}, {$placeholders})", $ids);
        } elseif ($driver === 'pgsql') {
            $placeholders = implode(',', array_fill(0, $count, '?'));
            $this->dataBuilder = $this->dataBuilder->orderByRaw("array_position(ARRAY[{$placeholders}], {$idCol})", $ids);
        } else {
            // SQLite and others — emulate with CASE
            $cases = [];
            for ($i = 0; $i < $count; $i++) {
                $cases[] = "WHEN ? THEN {$i}";
            }
            $caseExpr = "CASE {$idCol} " . implode(' ', $cases) . ' END';
            $this->dataBuilder = $this->dataBuilder->orderByRaw($caseExpr, $ids);
        }
    }

    /**
     * Resolve scoring config from $this->tables['scoring'].
     *
     * Supports two formats:
     *
     *  Long format:
     *      'scoring' => [
     *          'mode'    => 'replace' | 'complement',
     *          'columns' => ['product_name' => 5, 'sku' => 3, ...],
     *      ]
     *
     *  Shortcut format (treated as 'complement' mode):
     *      'scoring' => ['product_name' => 5, 'sku' => 3, ...]
     *
     * Returns null when scoring is not configured.
     */
    private function resolveScoringConfig(): ?array
    {
        if (!isset($this->tables['scoring'])) return null;

        $scoring = $this->tables['scoring'];
        if (!is_array($scoring) || empty($scoring)) return null;

        $isLongFormat = array_key_exists('columns', $scoring) || array_key_exists('mode', $scoring);

        if ($isLongFormat) {
            $columns = $scoring['columns'] ?? [];
            $mode    = $scoring['mode']    ?? 'complement';
        } else {
            $columns = $scoring;
            $mode    = 'complement';
        }

        if (!in_array($mode, ['replace', 'complement'], true)) {
            throw MrCatzException::invalidScoringMode($mode);
        }

        if (empty($columns)) return null;

        return ['mode' => $mode, 'columns' => $columns];
    }

    /**
     * Build the effective list of searchable columns with weights.
     * Each entry: ['key' => string, 'weight' => int].
     *
     * Resolution rules:
     *  - No scoring config       → all dataTableSet columns, weight = 1 (legacy)
     *  - mode 'replace'          → only scoring.columns, with custom weights
     *  - mode 'complement'       → all dataTableSet columns + scoring weights
     *                              applied as overrides; scoring keys not in
     *                              dataTableSet are appended (e.g. relation cols)
     */
    private function getEffectiveSearchColumns(): array
    {
        $defaultColumns = collect($this->dataTableSet)
            ->filter(fn($dtb) => $dtb['key'] != null)
            ->map(fn($dtb) => ['key' => $dtb['key'], 'weight' => 1])
            ->values()
            ->toArray();

        $scoring = $this->resolveScoringConfig();
        if ($scoring === null) return $defaultColumns;

        if ($scoring['mode'] === 'replace') {
            $result = [];
            foreach ($scoring['columns'] as $key => $weight) {
                $result[] = ['key' => $key, 'weight' => (int) $weight];
            }
            return $result;
        }

        // 'complement' — merge dataTableSet with scoring weight overrides
        $weightMap = $scoring['columns'];
        $result = [];
        $seen = [];

        foreach ($defaultColumns as $col) {
            $key = $col['key'];
            $result[] = ['key' => $key, 'weight' => (int) ($weightMap[$key] ?? 1)];
            $seen[$key] = true;
        }

        foreach ($weightMap as $key => $weight) {
            if (!isset($seen[$key])) {
                $result[] = ['key' => $key, 'weight' => (int) $weight];
            }
        }

        return $result;
    }

    /**
     * Resolve typo_tolerance config from $this->tables['typo_tolerance'].
     *
     * Supported formats:
     *   - true                                  → ['driver' => 'trigram', 'min_word_length' => 4]
     *   - false / not set / 'none' / null       → ['driver' => 'none', ...]
     *   - ['driver' => 'trigram', 'min_word_length' => 4]
     *   - ['driver' => 'pg_trgm', 'min_word_length' => 4]
     *
     * Validates the driver and DB compatibility (throws on mismatch).
     */
    private function resolveTypoToleranceConfig(): array
    {
        $default = ['driver' => 'none', 'min_word_length' => 4];

        if (!isset($this->tables['typo_tolerance'])) return $default;

        $raw = $this->tables['typo_tolerance'];

        if ($raw === true)  return ['driver' => 'trigram', 'min_word_length' => 4];
        if ($raw === false) return $default;
        if (!is_array($raw)) return $default;

        $driver = $raw['driver'] ?? 'trigram';
        $minLen = (int) ($raw['min_word_length'] ?? 4);

        if (!in_array($driver, ['none', 'trigram', 'pg_trgm'], true)) {
            throw MrCatzException::invalidTypoDriver($driver);
        }

        $config = ['driver' => $driver, 'min_word_length' => max(1, $minLen)];

        if ($driver !== 'none') {
            $this->validateTypoDriver($driver);
        }

        return $config;
    }

    /**
     * Runtime validation: pg_trgm requires a PostgreSQL connection.
     */
    private function validateTypoDriver(string $driver): void
    {
        if ($driver !== 'pg_trgm') return;

        $base = $this->getBaseDataBuilder();
        if (is_array($base)) return;

        $connection = $base instanceof EloquentBuilder
            ? $base->getQuery()->getConnection()
            : $base->getConnection();

        $actual = $connection->getDriverName();
        if ($actual !== 'pgsql') {
            throw MrCatzException::unsupportedDbForDriver('pg_trgm', $actual);
        }
    }

    /**
     * Generate unique character n-grams (default size 3) for fuzzy matching.
     * Returns empty array if word is shorter than the gram size.
     */
    public static function generateTrigrams(string $word, int $size = 3): array
    {
        $word = mb_strtolower($word);
        $len = mb_strlen($word);
        if ($len < $size) return [];

        $grams = [];
        for ($i = 0; $i <= $len - $size; $i++) {
            $grams[] = mb_substr($word, $i, $size);
        }
        return array_values(array_unique($grams));
    }

    private function applyOrdering(): void
    {
        if ($this->dataBuilder instanceof EloquentBuilder) {
            $this->dataBuilder->getQuery()->orders = null;
        } else {
            $this->dataBuilder->orders = null;
        }

        foreach ($this->dataTableSet as $dtb) {
            if ($dtb['key'] != null && $dtb['order'] != null) {
                $this->dataBuilder = $this->dataBuilder->orderBy($dtb['key'], $dtb['order']);
            }
        }

        // Scout-driven relevance order takes precedence; otherwise fall back
        // to the SQL match_count column produced by buildRelevanceSubquery().
        if ($this->scoutOrderedIds !== null) {
            $this->applyScoutOrderBy();
        } elseif ($this->search != '' && $this->tables != null && isset($this->tables['table_name'], $this->tables['table_id'])) {
            $this->dataBuilder = $this->dataBuilder->orderBy($this->tableFindName . '.match_count', 'desc');
        }

        if (count($this->order) == 0) {
            $this->dataBuilder = $this->dataBuilder->orderBy($this->defaultOrderBy, $this->defaultOrderDirection);
        } else {
            foreach ($this->order as $order) {
                $this->dataBuilder = $this->dataBuilder->orderBy($order['orderBy'], $order['direction']);
            }
        }
    }

    private function notifyDataLoaded(): void
    {
        $load = $this->onDataLoaded;
        if (isset($load)) { $load($this->dataBuilder, $this->data); }
    }

    private function executeQuery(): void
    {
        if ($this->usePagination) {
            $this->data = $this->dataBuilder->paginate($this->paginate, ['*'], $this->pageName);
        } else {
            if ($this->maxRows !== null) {
                $this->data = $this->dataBuilder->limit($this->maxRows + 1)->get();
                if ($this->data->count() > $this->maxRows) {
                    $this->hasMoreRows = true;
                    $this->data = $this->data->slice(0, $this->maxRows)->values();
                } else {
                    $this->hasMoreRows = false;
                }
            } else {
                $this->data = $this->dataBuilder->get();
            }
        }
        $this->data->paginateOptions = $this->paginateOptions;
        $this->data->hasData = $this->hasData();
    }

    public static function applySearchWhere(
        EloquentBuilder|QueryBuilder $query,
        string $search,
        array $searchableColumns,
        ?array $typoConfig = null
    ): EloquentBuilder|QueryBuilder {
        $words = array_filter(explode(' ', $search), fn($s) => !empty($s));
        if (empty($words)) return $query;

        $driver = $typoConfig['driver']          ?? 'none';
        $minLen = $typoConfig['min_word_length'] ?? 4;

        return $query->where(function ($q) use ($words, $searchableColumns, $driver, $minLen) {
            $first = true;
            foreach ($words as $word) {
                foreach ($searchableColumns as $col) {
                    $key = is_array($col) ? $col['key'] : $col;

                    // Always: exact substring match
                    if ($first) { $q->where($key, 'like', '%' . $word . '%'); $first = false; }
                    else { $q->orWhere($key, 'like', '%' . $word . '%'); }

                    // Typo tolerance: skip words shorter than min_word_length
                    if ($driver === 'none' || mb_strlen($word) < $minLen) continue;

                    if ($driver === 'trigram') {
                        foreach (self::generateTrigrams($word) as $gram) {
                            $q->orWhere($key, 'like', '%' . $gram . '%');
                        }
                    } elseif ($driver === 'pg_trgm') {
                        $q->orWhereRaw("LOWER({$key}) % ?", [mb_strtolower($word)]);
                    }
                }
            }
        });
    }

    private function buildRelevanceSubquery(array $keywords, array $searchableColumns, ?array $typoConfig = null): QueryBuilder
    {
        $tableName = $this->tables['table_name'];
        $tableId = $this->tables['table_id'];
        $caseStatements = [];
        $bindings = [];

        $typoDriver  = $typoConfig['driver']          ?? 'none';
        $typoMinLen  = $typoConfig['min_word_length'] ?? 4;
        $typoEnabled = $typoDriver !== 'none';

        // When fuzzy is on, scale exact-match weight so that one exact hit
        // always outranks any number of trigram-bonus hits for the same word.
        $exactMultiplier = $typoEnabled ? 100 : 1;

        foreach ($keywords as $keyword) {
            $lowerKeyword = mb_strtolower($keyword);
            $eligibleForFuzzy = $typoEnabled && mb_strlen($keyword) >= $typoMinLen;

            foreach ($searchableColumns as $col) {
                $key    = is_array($col) ? $col['key'] : $col;
                $weight = is_array($col) ? (int) ($col['weight'] ?? 1) : 1;
                $exactWeight = $weight * $exactMultiplier;

                // Exact match score
                $caseStatements[] = "SUM(CASE WHEN LOWER({$key}) LIKE ? THEN {$exactWeight} ELSE 0 END)";
                $bindings[] = '%' . $lowerKeyword . '%';

                if (!$eligibleForFuzzy) continue;

                // Fuzzy bonus: each trigram match contributes weight=1
                if ($typoDriver === 'trigram') {
                    foreach (self::generateTrigrams($keyword) as $gram) {
                        $caseStatements[] = "SUM(CASE WHEN LOWER({$key}) LIKE ? THEN 1 ELSE 0 END)";
                        $bindings[] = '%' . $gram . '%';
                    }
                } elseif ($typoDriver === 'pg_trgm') {
                    $caseStatements[] = "SUM(CASE WHEN LOWER({$key}) % ? THEN 1 ELSE 0 END)";
                    $bindings[] = $lowerKeyword;
                }
            }
        }

        $matchCountExpr = implode(' + ', $caseStatements) . ' AS match_count';
        $sub = DB::table($tableName)->select("{$tableName}.{$tableId}", DB::raw($matchCountExpr));

        $baseBuilder = $this->getBaseDataBuilder();
        $baseQuery = $baseBuilder instanceof EloquentBuilder ? $baseBuilder->getQuery() : $baseBuilder;
        if ($baseQuery->joins) { $sub->joins = $baseQuery->joins; }

        $sub = self::applySearchWhere($sub, implode(' ', $keywords), $searchableColumns, $typoConfig);
        foreach ($bindings as $binding) { $sub->addBinding($binding, 'select'); }

        return $sub->groupBy("{$tableName}.{$tableId}")->orderBy('match_count', 'desc');
    }

    // Column definitions

    public function withColumn(
        string $head,
        string $key,
        bool $uppercase = false,
        bool $th = false,
        bool $sort = true,
        string $gravity = 'left',
        bool $editable = false,
        bool $visible = true,
        ?string $rules = null,
        string $showOn = 'both'
    ): self {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => $key, 'index' => null, 'i' => $this->index, 'uppercase' => $uppercase, 'th' => $th, 'sort' => $sort, 'gravity' => $gravity, 'editable' => $editable, 'visible' => $visible, 'rules' => $rules, 'showOn' => $showOn];
        $this->callbacks[$this->index] = null;
        $this->index++;
        return $this;
    }

    public function withCustomColumn(string $head, ?callable $callback = null, ?string $key = null, bool $sort = true, bool $visible = true, string $showOn = 'both', ?string $type = null): self
    {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => $key, 'index' => null, 'i' => $this->index, 'uppercase' => false, 'th' => false, 'sort' => $sort, 'gravity' => 'left', 'editable' => false, 'visible' => $visible, 'showOn' => $showOn];
        if ($type !== null) {
            $this->dataTableSet[$this->index]['type'] = $type;
        }
        $this->callbacks[$this->index] = $callback;
        $this->index++;
        return $this;
    }

    public function withColumnIndex(string $head): self
    {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => null, 'index' => 'index', 'i' => $this->index, 'uppercase' => false, 'th' => false, 'sort' => false, 'gravity' => 'left', 'editable' => false, 'visible' => true];
        $this->callbacks[$this->index] = null;
        $this->index++;
        return $this;
    }

    /**
     * Register a built-in action column (edit / delete buttons).
     *
     * Side-effects:
     *  - Sets $hasEditAction / $hasDeleteAction on the engine so keyboard
     *    shortcuts (Enter to edit, Delete/Backspace to delete) only bind
     *    when the corresponding action is actually exposed to the user.
     *
     * Prefer this over manually calling `withCustomColumn('Aksi', fn($d, $i) =>
     * MrCatzDataTables::getActionView($d, $i, $editable, $deletable))`, which
     * renders the same buttons but leaves the engine unaware of them.
     */
    public function withActionColumn(string $head = null, \Closure|bool $editable = true, \Closure|bool $deletable = true): self
    {

        if(!$head){
            $head = mrcatz_lang('btn_action');
        }

        if ($editable)  $this->hasEditAction = true;
        if ($deletable) $this->hasDeleteAction = true;

        return $this->withCustomColumn(
            $head,
            function ($data, $i) use ($editable, $deletable) {
                $canEdit = $editable instanceof \Closure ? $editable($data, $i) : $editable;
                $canDelete = $deletable instanceof \Closure ? $deletable($data, $i) : $deletable;

                if (!$canEdit && !$canDelete) {
                    return '<span class="text-xs text-base-content/30">—</span>';
                }

                return self::getActionView($data, $i, $canEdit, $canDelete);
            },
            key: null,
            sort: false,
            type: 'action',
        );
    }

    public function getDataTableSet(): array { return $this->dataTableSet; }
    public function countColumn(): int { return count($this->dataTableSet); }
    public function countRow(): int { return count($this->data); }

    private function validateColumnIndex(int $i): void
    {
        if (!isset($this->dataTableSet[$i])) {
            throw MrCatzException::columnNotFound($i, count($this->dataTableSet));
        }
    }

    private function validateRowIndex(int $i): void
    {
        if (!isset($this->data[$i])) {
            throw MrCatzException::rowNotFound($i, count($this->data));
        }
    }

    public function getHead(int $i): string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['head']; }
    public function getKey(int $i): ?string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['key']; }
    public function getIndex(int $i): ?string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['index']; }
    public function getSort(int $i): bool { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['sort']; }
    public function getOrder(int $i): ?string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['order']; }
    public function isUppercase(int $i): bool { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['uppercase']; }
    public function isTH(int $i): bool { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['th']; }
    public function gravity(int $i): string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['gravity']; }
    public function isEditable(int $i): bool { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['editable'] ?? false; }
    public function isVisible(int $i): bool { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['visible'] ?? true; }
    public function getRules(int $i): ?string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['rules'] ?? null; }
    public function getShowOn(int $i): string { $this->validateColumnIndex($i); return $this->dataTableSet[$i]['showOn'] ?? 'both'; }

    public function getInlineValidationRules(): array
    {
        $rules = [];
        foreach ($this->dataTableSet as $col) {
            if (($col['editable'] ?? false) && !empty($col['rules']) && $col['key'] !== null) {
                $rules[$col['key']] = $col['rules'];
            }
        }
        return $rules;
    }

    public function getData(int $indexRow, int $indexColumn): mixed
    {
        $this->validateColumnIndex($indexColumn);
        $this->validateRowIndex($indexRow);

        if ($this->dataTableSet[$indexColumn]['key'] != null) {
            if ($this->callbacks[$indexColumn] != null) {
                return $this->callbacks[$indexColumn]($this->data[$indexRow], $indexRow);
            }
            $columnKey = $this->dataTableSet[$indexColumn]['key'];
            // Support table-prefixed keys (e.g. 'products.name' → pluck 'name')
            $pluckKey = str_contains($columnKey, '.') ? substr($columnKey, strrpos($columnKey, '.') + 1) : $columnKey;
            if (!isset($this->pluckCache[$pluckKey])) {
                $this->pluckCache[$pluckKey] = $this->data->pluck($pluckKey)->all();
            }
            $key = $this->pluckCache[$pluckKey][$indexRow] ?? null;
            return $this->setSearchWord($key);
        }
        if ($this->dataTableSet[$indexColumn]['index'] != null) {
            return ($indexRow + 1) + (($this->currentPage - 1) * $this->paginate);
        }
        if ($this->dataTableSet[$indexColumn]['key'] == null && $this->dataTableSet[$indexColumn]['index'] == null) {
            return $this->callbacks[$indexColumn]($this->data[$indexRow], $indexRow);
        }
    }

    /**
     * Highlight every occurrence of the current search keywords inside a
     * piece of text. Returns HTML.
     *
     * Two important properties:
     *   1. Case-insensitive matching, but the ORIGINAL CASE of the matched
     *      text is preserved in the output. Searching "cob" in "Coba banget"
     *      yields "<mark>Cob</mark>a banget" — not "COB" or "cob".
     *   2. The output uses a subtle DaisyUI warning-tinted span (matches
     *      whatever theme the consumer is using) with semi-bold weight and
     *      rounded edges. Looks aesthetic in both light and dark themes.
     */
    public function setSearchWord(?string $words): string
    {
        $words = $words ?? '';
        if ($words === '') return '';

        $escaped = e($words);

        // Markers — non-printable bytes that won't ever appear in user content,
        // and don't match any reasonable search keyword pattern.
        $startMarker = "\x00\x01MRCATZ_HL_START\x02\x00";
        $endMarker   = "\x00\x01MRCATZ_HL_END\x02\x00";

        $result = $escaped;
        $searchTerms = preg_split('/\s+/u', e($this->search ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($searchTerms as $search) {
            $search = trim($search);
            if ($search === '') continue;

            // Case-insensitive Unicode-aware match. Use preg_replace_callback so
            // we can echo back the ACTUAL matched substring (preserving its case)
            // instead of replacing it with a transformed version of the needle.
            $pattern = '/' . preg_quote($search, '/') . '/iu';
            $result = preg_replace_callback($pattern, function ($m) use ($startMarker, $endMarker) {
                return $startMarker . $m[0] . $endMarker;
            }, $result) ?? $result;
        }

        // Replace markers with the final HTML once (avoids double-wrapping if the
        // same fragment matches multiple search terms).
        //
        // We use INLINE styles instead of Tailwind utility classes because the
        // highlight markup is injected at runtime — Tailwind's content scanner
        // never sees these tokens in any blade file, so utility classes like
        // `bg-warning/30` would silently get tree-shaken out and the highlight
        // would render with no visible background.
        //
        // The colors are exposed as CSS custom properties so users can theme
        // them globally without touching the package:
        //
        //     :root {
        //         --mrcatz-highlight-bg: rgba(250, 204, 21, 0.5);
        //         --mrcatz-highlight-color: inherit;
        //     }
        $style = implode('', [
            'background-color:var(--mrcatz-highlight-bg,rgba(250,204,21,0.4));',
            'color:var(--mrcatz-highlight-color,inherit);',
            'border-radius:0.25rem;',
            'padding:0 0.15rem;',
            'font-weight:600;',
        ]);

        $outputWords = str_replace(
            [$startMarker, $endMarker],
            ['<span class="mrcatz-search-highlight" style="' . $style . '">', '</span>'],
            $result
        );

        return trim($outputWords);
    }

    // Bulk & Expand

    public function enableBulk(?callable $callback = null): self
    {
        $this->bulkCallback = $callback ? \Closure::fromCallable($callback) : fn($data, $i) => true;
        return $this;
    }

    public function isBulkEnabled(int $indexRow): bool
    {
        if ($this->bulkCallback === null) return true;
        return ($this->bulkCallback)($this->data[$indexRow], $indexRow);
    }

    public function enableEditable(?callable $callback = null): self
    {
        $this->editableCallback = $callback ? \Closure::fromCallable($callback) : fn($data, $i) => true;
        return $this;
    }

    public function hasEditableCallback(): bool { return $this->editableCallback !== null; }

    public function isEditableRow(int $indexRow, ?string $columnKey = null): bool
    {
        if ($this->editableCallback === null) return true;
        return ($this->editableCallback)($this->data[$indexRow], $indexRow, $columnKey);
    }

    public function enableExpand(callable $callback): self
    {
        $this->expandCallback = \Closure::fromCallable($callback);
        return $this;
    }

    public function hasExpand(): bool { return $this->expandCallback !== null; }

    public function isExpandEnabled(int $indexRow): bool
    {
        $this->validateRowIndex($indexRow);
        if (!$this->expandCallback) return false;
        return ($this->expandCallback)($this->data[$indexRow], $indexRow) !== null;
    }

    public function getExpandContent(int $indexRow): string
    {
        $this->validateRowIndex($indexRow);
        if (!$this->expandCallback) return '';
        return ($this->expandCallback)($this->data[$indexRow], $indexRow) ?? '';
    }

    public function setExportData(mixed $data): void
    {
        $this->pluckCache = [];
        $this->data = $data;
    }

    public function getRowRawData(int $indexRow): mixed { $this->validateRowIndex($indexRow); return $this->data[$indexRow]; }
    public function getDatas(): mixed { return $this->data; }

    public function links(?string $view = null): mixed
    {
        return $this->data->links($view);
    }

    public static function getActionView(mixed $data, int $i, bool $editable = true, bool $deletable = true): View
    {
        return view('mrcatz::components.ui.datatable-action', [
            'data' => json_encode($data),
            'index' => $i,
            'editable' => $editable,
            'deletable' => $deletable,
        ]);
    }

    /**
     * Add an image column with preview and clickable lightbox.
     *
     * @param string $head         Column header text
     * @param string $key          DB column key containing image URL/path
     * @param int    $width        Preview width in px (default: 40)
     * @param int    $height       Preview height in px (default: 40)
     * @param string $previewClass Tailwind classes for shape/decoration (default: 'rounded-full')
     * @param string|null $fallback  Fallback DB column for initial letter when no image
     * @param bool   $sort         Sortable column
     * @param bool   $visible      Column visibility
     * @param string $showOn       'both', 'desktop', 'mobile'
     */
    /**
     * Add an image column with preview and clickable lightbox.
     *
     * @param string      $head         Column header
     * @param string      $key          DB column containing image path/URL
     * @param int         $width        Preview width px
     * @param int         $height       Preview height px
     * @param string      $previewClass Tailwind classes for shape/decoration
     * @param string|null $fallback     DB column for initial letter fallback
     * @param string|null $urlPrefix    URL prefix mode (null = use config('mrcatz.url_prefix')):
     *                                  - null: use config default
     *                                  - 'storage': asset('storage/' . $value)
     *                                  - 'public': asset($value)
     *                                  - 'https://...' or 'http://...': prefix . $value
     *                                  - '' (empty string): use DB value as-is (already full URL)
     * @param bool        $sort
     * @param bool        $visible
     * @param string      $showOn
     */
    public function withColumnImage(
        string $head,
        string $key,
        int $width = 40,
        int $height = 40,
        string $previewClass = 'rounded-full',
        ?string $fallback = null,
        ?string $urlPrefix = null,
        bool $sort = false,
        bool $visible = true,
        string $showOn = 'both',
        string $gravity = 'center',
    ): self {
        $urlPrefix = $urlPrefix ?? config('mrcatz.url_prefix', 'storage');
        $imgMeta = compact('width', 'height', 'previewClass', 'fallback');
        $this->dataTableSet[$this->index] = [
            'head' => $head, 'order' => null, 'key' => $key, 'index' => null,
            'i' => $this->index, 'uppercase' => false, 'th' => false,
            'sort' => $sort, 'gravity' => $gravity, 'editable' => false,
            'visible' => $visible, 'showOn' => $showOn,
            'type' => 'image', 'imageMeta' => $imgMeta,
        ];
        $this->callbacks[$this->index] = function ($data, $i) use ($key, $width, $height, $previewClass, $fallback, $urlPrefix, $gravity) {
            $url = self::resolveImageUrl($data->{$key} ?? null, $urlPrefix);
            $fallbackText = $fallback ? ($data->{$fallback} ?? null) : null;
            return self::getImageView($url, $i, $width, $height, $previewClass, $fallbackText, $key, $gravity);
        };
        $this->index++;
        return $this;
    }

    /**
     * Resolve image URL based on prefix mode.
     */
    public static function resolveImageUrl(?string $value, ?string $prefix = null): ?string
    {
        if (!$value) return null;
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value; // already absolute
        }

        $prefix = $prefix ?? config('mrcatz.url_prefix', 'storage');

        return match ($prefix) {
            'storage' => asset('storage/' . $value),
            'public'  => asset($value),
            ''        => $value,
            default   => str_starts_with($prefix, 'http') ? rtrim($prefix, '/') . '/' . $value : asset($prefix . '/' . $value),
        };
    }

    public function getColumnType(int $i): ?string
    {
        return $this->dataTableSet[$i]['type'] ?? null;
    }

    public function getImageMeta(int $i): ?array
    {
        return $this->dataTableSet[$i]['imageMeta'] ?? null;
    }

    /**
     * Render an image cell with lightbox support.
     */
    public static function getImageView(
        ?string $url,
        int $index,
        int $width = 40,
        int $height = 40,
        string $previewClass = 'rounded-full',
        ?string $fallback = null,
        string $columnKey = 'image',
        string $gravity = 'center',
        ?string $urlPrefix = null,
    ): string {
        // When a urlPrefix is supplied, run the raw value through resolveImageUrl
        // so callers can pass a bare DB field (e.g. "abc.jpg") and get back a
        // fully-qualified URL the same way withColumnImage() does. Leaving
        // urlPrefix null keeps the legacy pure-renderer behavior: $url is used
        // as-is, whatever the caller pre-resolved themselves.
        if ($urlPrefix !== null) {
            $url = self::resolveImageUrl($url, $urlPrefix);
        }

        return view('mrcatz::components.ui.datatable-image', [
            'url' => $url,
            'index' => $index,
            'width' => $width,
            'height' => $height,
            'previewClass' => $previewClass,
            'fallback' => $fallback,
            'columnKey' => $columnKey,
            'gravity' => $gravity,
        ])->render();
    }

    /**
     * Render expand view with support for text, image, and button fields.
     *
     * Field formats:
     *   'Label' => 'db_key'                                    // simple text
     *   'Label' => ['key' => 'db_key', 'type' => 'image', 'width' => 64, 'height' => 64, 'previewClass' => 'rounded-lg']
     *   'Label' => ['type' => 'button', 'label' => 'Download', 'url' => $data->file_url, 'icon' => 'download', 'style' => 'primary']
     *   'Label' => ['type' => 'button', 'label' => 'Download', 'url' => fn($data) => route('download', $data->id)]
     */
    public static function getExpandView(mixed $data, array $fields): string
    {
        $mapped = [];
        foreach ($fields as $label => $value) {
            if (is_string($value)) {
                // Simple text field
                $mapped[] = [
                    'label' => $label,
                    'type' => 'text',
                    'value' => $data->{$value} ?? '-',
                ];
            } elseif (is_array($value)) {
                $type = $value['type'] ?? 'text';

                if ($type === 'image') {
                    $key = $value['key'] ?? '';
                    $imgPrefix = $value['urlPrefix'] ?? null;
                    $url = self::resolveImageUrl($data->{$key} ?? null, $imgPrefix);
                    $fallbackKey = $value['fallback'] ?? null;
                    $mapped[] = [
                        'label' => $label,
                        'type' => 'image',
                        'url' => $url,
                        'width' => $value['width'] ?? 64,
                        'height' => $value['height'] ?? 64,
                        'previewClass' => $value['previewClass'] ?? 'rounded-lg',
                        'fallback' => $fallbackKey ? ($data->{$fallbackKey} ?? null) : null,
                    ];
                } elseif ($type === 'button' || $type === 'link') {
                    $url = $value['url'] ?? '#';
                    if ($url instanceof \Closure) {
                        $url = $url($data);
                    }
                    $target = $value['target'] ?? null;
                    if (!empty($value['newTab'])) $target = '_blank';
                    $mapped[] = [
                        'label' => $label,
                        'type' => $type,
                        'buttonLabel' => $value['label'] ?? $label,
                        'url' => $url,
                        'icon' => $value['icon'] ?? null,
                        'style' => $value['style'] ?? ($type === 'link' ? 'ghost' : 'primary'),
                        'download' => $value['download'] ?? false,
                        'target' => $target,
                    ];
                } elseif ($type === 'html') {
                    $content = $value['content'] ?? '';
                    if ($content instanceof \Closure) {
                        $content = $content($data);
                    }
                    $mapped[] = [
                        'label' => $label,
                        'type' => 'html',
                        'content' => $content,
                    ];
                } else {
                    // Text with key
                    $key = $value['key'] ?? '';
                    $mapped[] = [
                        'label' => $label,
                        'type' => 'text',
                        'value' => $data->{$key} ?? '-',
                    ];
                }
            }
        }
        return view('mrcatz::components.ui.datatable-expand', ['fields' => $mapped])->render();
    }
}
