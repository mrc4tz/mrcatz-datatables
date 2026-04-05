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
