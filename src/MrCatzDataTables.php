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
    public function hasData(): bool { return count($this->data) > 0; }

    public function build(): self
    {
        $this->dataBuilder = clone $this->getBaseDataBuilder();
        $this->applyFilters();
        $this->applySearch();
        $this->applyOrdering();
        $this->notifyDataLoaded();
        $this->executeQuery();
        return $this;
    }

    private function applyFilters(): void
    {
        foreach ($this->keyValue as $x => $kv) {
            if (!empty($kv['value'])) {
                if (Arr::exists($this->callbackFilters, $x) && $this->callbackFilters[$x] != null) {
                    $this->dataBuilder = $this->callbackFilters[$x]($this->dataBuilder, $kv['value']);
                } else {
                    if ($kv['key'] != '-') {
                        $this->dataBuilder = $this->dataBuilder->where($kv['key'], $kv['condition'], $kv['value']);
                    }
                }
            } else {
                if (Arr::exists($this->callbackFilters, $x) && $this->callbackFilters[$x] != null) {
                    $this->dataBuilder = $this->callbackFilters[$x]($this->dataBuilder, $kv['value']);
                }
            }
        }
    }

    private function applySearch(): void
    {
        if ($this->search == '') return;

        $searchableColumns = collect($this->dataTableSet)->filter(fn($dtb) => $dtb['key'] != null)->values()->toArray();
        $explodedSearch = array_filter(explode(" ", $this->search), fn($s) => !empty($s));

        if (empty($searchableColumns) || count($explodedSearch) == 0) return;

        $this->dataBuilder = self::applySearchWhere($this->dataBuilder, $this->search, $searchableColumns);

        if ($this->tables != null) {
            $relevanceSub = $this->buildRelevanceSubquery($explodedSearch, $searchableColumns);
            $findName = $this->tableFindName;
            $tableId = $this->tables['table_id'];
            $tableName = $this->tables['table_name'];

            $this->dataBuilder = $this->dataBuilder
                ->joinSub($relevanceSub, $findName, function ($join) use ($findName, $tableId, $tableName) {
                    $join->on("{$findName}.{$tableId}", '=', "{$tableName}.{$tableId}");
                });
        }
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

        if ($this->search != '' && $this->tables != null) {
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
            $this->data = $this->dataBuilder->get();
        }
        $this->data->paginateOptions = $this->paginateOptions;
        $this->data->hasData = $this->hasData();
    }

    public static function applySearchWhere(
        EloquentBuilder|QueryBuilder $query,
        string $search,
        array $searchableColumns
    ): EloquentBuilder|QueryBuilder {
        $words = array_filter(explode(' ', $search), fn($s) => !empty($s));
        if (empty($words)) return $query;

        return $query->where(function ($q) use ($words, $searchableColumns) {
            $first = true;
            foreach ($words as $word) {
                foreach ($searchableColumns as $col) {
                    $key = is_array($col) ? $col['key'] : $col;
                    if ($first) { $q->where($key, 'like', '%' . $word . '%'); $first = false; }
                    else { $q->orWhere($key, 'like', '%' . $word . '%'); }
                }
            }
        });
    }

    private function buildRelevanceSubquery(array $keywords, array $searchableColumns): QueryBuilder
    {
        $tableName = $this->tables['table_name'];
        $tableId = $this->tables['table_id'];
        $caseStatements = [];
        $bindings = [];

        foreach ($keywords as $keyword) {
            foreach ($searchableColumns as $dtb) {
                $key = is_array($dtb) ? $dtb['key'] : $dtb;
                $caseStatements[] = "SUM(CASE WHEN LOWER({$key}) LIKE ? THEN 1 ELSE 0 END)";
                $bindings[] = '%' . mb_strtolower($keyword) . '%';
            }
        }

        $matchCountExpr = implode(' + ', $caseStatements) . ' AS match_count';
        $sub = DB::table($tableName)->select("{$tableName}.{$tableId}", DB::raw($matchCountExpr));

        $baseBuilder = $this->getBaseDataBuilder();
        $baseQuery = $baseBuilder instanceof EloquentBuilder ? $baseBuilder->getQuery() : $baseBuilder;
        if ($baseQuery->joins) { $sub->joins = $baseQuery->joins; }

        $sub = self::applySearchWhere($sub, implode(' ', $keywords), $searchableColumns);
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

    public function withCustomColumn(string $head, ?callable $callback = null, ?string $key = null, bool $sort = true, bool $visible = true, string $showOn = 'both'): self
    {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => $key, 'index' => null, 'i' => $this->index, 'uppercase' => false, 'th' => false, 'sort' => $sort, 'gravity' => 'left', 'editable' => false, 'visible' => $visible, 'showOn' => $showOn];
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
            $key = $this->data->pluck($pluckKey)[$indexRow];
            return $this->setSearchWord($key);
        }
        if ($this->dataTableSet[$indexColumn]['index'] != null) {
            return ($indexRow + 1) + (($this->currentPage - 1) * $this->paginate);
        }
        if ($this->dataTableSet[$indexColumn]['key'] == null && $this->dataTableSet[$indexColumn]['index'] == null) {
            return $this->callbacks[$indexColumn]($this->data[$indexRow], $indexRow);
        }
    }

    public function setSearchWord(string $words): string
    {
        $start = "{-------------##*##*------------}";
        $end = "{-------------##!##!------------}";
        $escapedWords = e($words);
        $newWords = $escapedWords;

        foreach (explode(" ", e($this->search)) as $search) {
            if (empty($search)) continue;
            $outputWordsTemp = "";
            foreach (explode(" ", $newWords) as $word) {
                if (str_contains(strtolower($word), strtolower($search))) {
                    $outputWordsTemp .= " " . str_ireplace($search, $start . strtoupper($search) . $end, $word);
                } else {
                    $outputWordsTemp .= " " . $word;
                }
            }
            $newWords = $outputWordsTemp;
        }

        $outputWords = str_replace($start, "<span class='font-extrabold'>", $newWords);
        $outputWords = str_replace($end, "</span>", $outputWords);
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

    public static function getExpandView(mixed $data, array $fields): string
    {
        $mapped = [];
        foreach ($fields as $label => $key) {
            $mapped[] = [
                'label' => $label,
                'value' => $data->{$key} ?? '-',
            ];
        }
        return view('mrcatz::components.ui.datatable-expand', ['fields' => $mapped])->render();
    }
}
