<?php

namespace MrCatz\DataTable;

class MrCatzDataTableFilter
{
    private string $id;
    private string|iterable $data;
    private string $value;
    private string $option;
    private string $key;
    private string $label;
    private string $condition;
    private ?\Closure $callback = null;
    private ?array $dataFilter = null;
    private bool $show;

    /** Filter widget type. 'select' (default) | 'date' | 'date_range' | 'check' */
    private string $type = 'select';

    /** Date format. '' (non-date) | 'date' | 'datetime' | 'time' | 'time_hm' | 'month_year' | 'year' */
    private string $format = '';

    /** Optional min/max constraints. Format must match `format`. */
    private ?string $minDate = null;
    private ?string $maxDate = null;

    /** Check filter: enable Include/Exclude mode toggle in the popover. */
    private bool $allowExclude = false;

    /** Check filter: show in-popover search when option count exceeds this threshold. Null = never. */
    private ?int $searchThreshold = 5;

    /** Whether this instance was built by a *WithCallback factory — used to reject allowExclude(). */
    private bool $isCallbackVariant = false;

    private const VALID_DATE_FORMATS = ['date', 'datetime', 'time', 'time_hm', 'month_year', 'year'];
    private const VALID_DATE_CONDITIONS = ['=', '!=', '<>', '>', '<', '>=', '<='];
    private const VALID_CHECK_CONDITIONS = ['whereIn', 'whereNotIn'];

    public static function create(
        string $id,
        string $label,
        string|iterable $data,
        string $value,
        string $option,
        string $key,
        bool $show = true,
        string $condition = '='
    ): self {
        $dataFilter = new self();
        $dataFilter->id = $id;
        $dataFilter->data = $data;
        $dataFilter->value = $value;
        $dataFilter->option = $option;
        $dataFilter->key = $key;
        $dataFilter->label = $label;
        $dataFilter->condition = $condition;
        $dataFilter->callback = null;
        $dataFilter->show = $show;
        return $dataFilter;
    }

    public static function createWithCallback(
        string $id,
        string $label,
        string|iterable $data,
        string $value,
        string $option,
        callable $callback,
        bool $show = true
    ): self {
        $dataFilter = new self();
        $dataFilter->id = $id;
        $dataFilter->data = $data;
        $dataFilter->value = $value;
        $dataFilter->option = $option;
        $dataFilter->key = '-';
        $dataFilter->label = $label;
        $dataFilter->condition = '-';
        $dataFilter->callback = \Closure::fromCallable($callback);
        $dataFilter->show = $show;
        return $dataFilter;
    }

    /**
     * Single date filter with operator + key. Engine applies the filter using
     * Laravel's portable date helpers (whereDate / whereYear / etc.) so the
     * SQL works across MySQL, PostgreSQL, and SQLite.
     */
    public static function createDate(
        string $id,
        string $label,
        string $key,
        string $format = 'date',
        string $condition = '=',
        ?string $minDate = null,
        ?string $maxDate = null,
        bool $show = true
    ): self {
        self::validateDateFormat($format);
        self::validateDateCondition($condition);

        $f = new self();
        $f->id        = $id;
        $f->label     = $label;
        $f->key       = $key;
        $f->condition = $condition;
        $f->type      = 'date';
        $f->format    = $format;
        $f->minDate   = $minDate;
        $f->maxDate   = $maxDate;
        $f->show      = $show;
        $f->data      = [];
        $f->value     = '';
        $f->option    = '';
        return $f;
    }

    /**
     * Single date filter that delegates to a user-provided callback. Use this
     * when the SQL is non-trivial (joins, custom comparisons, etc).
     * The callback receives ($query, $value) where $value is the picked date.
     */
    public static function createDateWithCallback(
        string $id,
        string $label,
        callable $callback,
        string $format = 'date',
        ?string $minDate = null,
        ?string $maxDate = null,
        bool $show = true
    ): self {
        self::validateDateFormat($format);

        $f = new self();
        $f->id        = $id;
        $f->label     = $label;
        $f->key       = '-';
        $f->condition = '-';
        $f->type      = 'date';
        $f->format    = $format;
        $f->minDate   = $minDate;
        $f->maxDate   = $maxDate;
        $f->callback  = \Closure::fromCallable($callback);
        $f->show      = $show;
        $f->data      = [];
        $f->value     = '';
        $f->option    = '';
        return $f;
    }

    /**
     * Date range filter with key. Renders two date inputs (from / to). Engine
     * applies open-ended ranges:
     *   - both set     → whereDate(>= from) AND whereDate(<= to)
     *   - only from    → whereDate(>= from)
     *   - only to      → whereDate(<= to)
     *   - both empty   → no-op
     *
     * Auto-swaps if user picks `to` earlier than `from`.
     */
    public static function createDateRange(
        string $id,
        string $label,
        string $key,
        string $format = 'date',
        ?string $minDate = null,
        ?string $maxDate = null,
        bool $show = true
    ): self {
        self::validateDateFormat($format);

        $f = new self();
        $f->id        = $id;
        $f->label     = $label;
        $f->key       = $key;
        $f->condition = '-';
        $f->type      = 'date_range';
        $f->format    = $format;
        $f->minDate   = $minDate;
        $f->maxDate   = $maxDate;
        $f->show      = $show;
        $f->data      = [];
        $f->value     = '';
        $f->option    = '';
        return $f;
    }

    /**
     * Date range filter with callback. The callback receives
     * ($query, ['from' => ..., 'to' => ...]) where either part may be null
     * for open-ended ranges.
     */
    public static function createDateRangeWithCallback(
        string $id,
        string $label,
        callable $callback,
        string $format = 'date',
        ?string $minDate = null,
        ?string $maxDate = null,
        bool $show = true
    ): self {
        self::validateDateFormat($format);

        $f = new self();
        $f->id        = $id;
        $f->label     = $label;
        $f->key       = '-';
        $f->condition = '-';
        $f->type      = 'date_range';
        $f->format    = $format;
        $f->minDate   = $minDate;
        $f->maxDate   = $maxDate;
        $f->callback  = \Closure::fromCallable($callback);
        $f->show      = $show;
        $f->data      = [];
        $f->value     = '';
        $f->option    = '';
        return $f;
    }

    /**
     * Multi-select checkbox filter. Renders a popover with a checkbox list;
     * engine applies `whereIn` (default) or `whereNotIn` when Include/Exclude
     * mode is toggled via `->allowExclude()`.
     *
     * Empty selection (no checkbox ticked) is a no-op.
     */
    public static function createCheck(
        string $id,
        string $label,
        string|iterable $data,
        string $value,
        string $option,
        string $key,
        string $condition = 'whereIn',
        bool $show = true
    ): self {
        self::validateCheckCondition($condition);

        $f = new self();
        $f->id        = $id;
        $f->label     = $label;
        $f->data      = $data;
        $f->value     = $value;
        $f->option    = $option;
        $f->key       = $key;
        $f->condition = $condition;
        $f->type      = 'check';
        $f->show      = $show;
        return $f;
    }

    /**
     * Multi-select checkbox filter that delegates to a user-provided callback.
     * Use this when the SQL is non-trivial (whereHas, joins, custom predicates).
     * The callback receives ($query, array $values) where $values is the list
     * of selected option values. An empty array is still passed to the callback
     * (for consistency with date/date_range callback variants).
     *
     * `->allowExclude()` is rejected on this variant — the callback owns the
     * include/exclude logic.
     */
    public static function createCheckWithCallback(
        string $id,
        string $label,
        string|iterable $data,
        string $value,
        string $option,
        callable $callback,
        bool $show = true
    ): self {
        $f = new self();
        $f->id                = $id;
        $f->label             = $label;
        $f->data              = $data;
        $f->value             = $value;
        $f->option            = $option;
        $f->key               = '-';
        $f->condition         = '-';
        $f->type              = 'check';
        $f->callback          = \Closure::fromCallable($callback);
        $f->show              = $show;
        $f->isCallbackVariant = true;
        return $f;
    }

    /**
     * Enable the Include/Exclude mode toggle in a check filter popover.
     * When Exclude is active, engine flips `whereIn` → `whereNotIn`.
     *
     * Rejected on `createCheckWithCallback` — the callback defines its own SQL.
     */
    public function allowExclude(): self
    {
        if ($this->isCallbackVariant) {
            throw \MrCatz\DataTable\Exceptions\MrCatzException::allowExcludeOnCallback($this->id);
        }
        $this->allowExclude = true;
        return $this;
    }

    /**
     * Show the in-popover search box when the number of options exceeds $count.
     * Pass `null` to never show the search (regardless of option count).
     * Default threshold: 5 (aligned with the popover list visual scroll break).
     */
    public function allowSearchWhen(?int $count = 5): self
    {
        $this->searchThreshold = $count;
        return $this;
    }

    private static function validateDateFormat(string $format): void
    {
        if (!in_array($format, self::VALID_DATE_FORMATS, true)) {
            throw \MrCatz\DataTable\Exceptions\MrCatzException::invalidDateFormat($format);
        }
    }

    private static function validateDateCondition(string $condition): void
    {
        if (!in_array($condition, self::VALID_DATE_CONDITIONS, true)) {
            throw \MrCatz\DataTable\Exceptions\MrCatzException::invalidDateCondition($condition);
        }
    }

    private static function validateCheckCondition(string $condition): void
    {
        if (!in_array($condition, self::VALID_CHECK_CONDITIONS, true)) {
            throw \MrCatz\DataTable\Exceptions\MrCatzException::invalidCheckCondition($condition);
        }
    }

    public function get(): self
    {
        $data = self::normalizeData($this->data);
        $this->dataFilter = [
            'id' => $this->id,
            'label' => $this->label,
            'value' => $this->value,
            'option' => $this->option,
            'key' => $this->key,
            'data' => $data,
            'condition' => $this->condition,
            'show' => $this->show,
            'type' => $this->type,
            'format' => $this->format,
            'min_date' => $this->minDate,
            'max_date' => $this->maxDate,
            'allow_exclude' => $this->allowExclude,
            'search_threshold' => $this->searchThreshold,
        ];
        return $this;
    }

    public function getDataFilter(): ?array
    {
        if ($this->dataFilter === null) {
            throw new \MrCatz\DataTable\Exceptions\MrCatzException(
                "Filter [{$this->id}] not initialized. Did you forget to call ->get()?"
            );
        }
        return $this->dataFilter;
    }
    public function getCallback(): ?callable { return $this->callback; }

    /**
     * Normalize filter data into a list of associative arrays.
     *
     * Accepts:
     *  - JSON string                       → decoded
     *  - array of arrays                   → as-is
     *  - array of stdClass / any object    → cast to array
     *  - Eloquent Model / Collection       → via toArray()
     *  - Traversable (any iterable)        → iterated, items normalized per rules above
     *
     * This lets callers pass `DB::table(...)->get()` or `Model::all()` directly
     * without manual `->map(fn($r) => (array)$r)` gymnastics.
     */
    private static function normalizeData(string|iterable $raw): array
    {
        // JSON string → decode
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        // Collection / any object with toArray() is fine as a wrapper, but we still
        // need to normalize inner items (Collection::toArray() on a Collection of
        // Models returns arrays, but on a Collection of stdClass it returns stdClass).
        if (!is_array($raw) && $raw instanceof \Traversable) {
            $raw = iterator_to_array($raw);
        }

        $normalized = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $normalized[] = $item;
            } elseif (is_object($item) && method_exists($item, 'toArray')) {
                // Eloquent Model, Collection item, etc.
                $normalized[] = $item->toArray();
            } elseif (is_object($item)) {
                // stdClass or any plain object
                $normalized[] = (array) $item;
            } else {
                // scalar / null — skip, filter rows must be key/value maps
                continue;
            }
        }

        return $normalized;
    }
}
