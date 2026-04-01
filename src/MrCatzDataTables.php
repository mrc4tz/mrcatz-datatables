<?php

namespace MrCatz\DataTable;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MrCatzDataTables
{
    private $index = 0;
    public $dataTableSet = [];
    private $data = [];
    private $callbacks = [];
    private $search = '';
    private $keyValue = [];
    private $callbackFilters = [];
    private $paginate = 5;
    private $defaultOrderBy = 'created_at';
    private $defaultOrderDirection = 'desc';
    private $order = [];
    public $dataBuilder;
    private $baseDataBuilder;
    public $paginateOptions = [5, 10, 15, 20];
    public $usePagination = true;
    private $tables = null;
    private $tableFindName = "table_find_765678912";
    private $pageName = 'page';
    private $onDataLoaded = null;
    private $currentPage = 1;
    private $bulkCallback = null;
    private $expandCallback = null;

    public static function with($data, $paginateOptions = [5, 10, 15, 20], $paginate = null, $usePagination = true, $pageName = 'page', $onDataLoaded = null)
    {
        $dt = new MrCatzDataTables();
        $dt->setBaseDataBuilder($data);
        $dt->paginateOptions = $paginateOptions;
        $dt->paginate = $paginate ?? $paginateOptions[0];
        $dt->usePagination = $usePagination;
        $dt->pageName = $pageName;
        $dt->onDataLoaded = $onDataLoaded;
        return $dt;
    }

    public function setDefaultOrder($defaultOrderBy, $defaultOrderDirection)
    {
        $this->defaultOrderBy = $defaultOrderBy;
        $this->defaultOrderDirection = $defaultOrderDirection;
        return $this;
    }

    public function getPageName() { return $this->pageName; }

    public function addOrderBy($orderBy, $orderDirection)
    {
        $this->order[] = ['orderBy' => $orderBy, 'direction' => $orderDirection];
        return $this;
    }

    public function getPaginate() { return $this->paginate; }
    public function setBaseDataBuilder($baseDataBuilder) { $this->baseDataBuilder = $baseDataBuilder; }
    public function getBaseDataBuilder() { return $this->baseDataBuilder; }

    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
        return $this;
    }

    public function setOrderByKey($key, $order)
    {
        for ($i = 0; $i < count($this->dataTableSet); $i++) {
            $this->dataTableSet[$i]['order'] = ($this->dataTableSet[$i]['key'] == $key) ? $order : null;
        }
        return $this;
    }

    public function setSearch($search) { $this->search = $search; return $this; }

    public function setFilters($keyValue, $callbackFilters)
    {
        $this->keyValue = $keyValue;
        $this->callbackFilters = $callbackFilters;
        return $this;
    }

    public function setConfig($tables)
    {
        if ($tables != null) $this->tables = $tables;
        return $this;
    }

    public function setPaginate($perPage) { $this->paginate = (int) $perPage; return $this; }
    public function hasData() { return count($this->data) > 0; }

    public function build()
    {
        $this->dataBuilder = clone $this->getBaseDataBuilder();
        $this->applyFilters();
        $this->applySearch();
        $this->applyOrdering();
        $this->notifyDataLoaded();
        $this->executeQuery();
        return $this;
    }

    private function applyFilters()
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

    private function applySearch()
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

    private function applyOrdering()
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

    private function notifyDataLoaded()
    {
        $load = $this->onDataLoaded;
        if (isset($load)) { $load($this->dataBuilder, $this->data); }
    }

    private function executeQuery()
    {
        if ($this->usePagination) {
            $this->data = $this->dataBuilder->paginate($this->paginate, ['*'], $this->pageName);
        } else {
            $this->data = $this->dataBuilder->get();
        }
        $this->data->paginateOptions = $this->paginateOptions;
        $this->data->hasData = $this->hasData();
    }

    public static function applySearchWhere($query, string $search, array $searchableColumns)
    {
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

    private function buildRelevanceSubquery(array $keywords, array $searchableColumns)
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

    public function withColumn($head, $key, $uppercase = false, $th = false, $sort = true, $gravity = 'left')
    {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => $key, 'index' => null, 'i' => $this->index, 'uppercase' => $uppercase, 'th' => $th, 'sort' => $sort, 'gravity' => $gravity];
        $this->callbacks[$this->index] = null;
        $this->index++;
        return $this;
    }

    public function withCustomColumn($head, ?callable $callback = null, $key = null, $sort = true)
    {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => $key, 'index' => null, 'i' => $this->index, 'uppercase' => false, 'th' => false, 'sort' => $sort, 'gravity' => 'left'];
        $this->callbacks[$this->index] = $callback;
        $this->index++;
        return $this;
    }

    public function withColumnIndex($head)
    {
        $this->dataTableSet[$this->index] = ['head' => $head, 'order' => null, 'key' => null, 'index' => 'index', 'i' => $this->index, 'uppercase' => false, 'th' => false, 'sort' => false, 'gravity' => 'left'];
        $this->callbacks[$this->index] = null;
        $this->index++;
        return $this;
    }

    public function getDataTableSet() { return $this->dataTableSet; }
    public function countColumn() { return count($this->dataTableSet); }
    public function countRow() { return count($this->data); }
    public function getHead($i) { return $this->dataTableSet[$i]['head']; }
    public function getKey($i) { return $this->dataTableSet[$i]['key']; }
    public function getIndex($i) { return $this->dataTableSet[$i]['index']; }
    public function getSort($i) { return $this->dataTableSet[$i]['sort']; }
    public function getOrder($i) { return $this->dataTableSet[$i]['order']; }
    public function isUppercase($i) { return $this->dataTableSet[$i]['uppercase']; }
    public function isTH($i) { return $this->dataTableSet[$i]['th']; }
    public function gravity($i) { return $this->dataTableSet[$i]['gravity']; }

    public function getData($indexRow, $indexColumn)
    {
        if ($this->dataTableSet[$indexColumn]['key'] != null) {
            if ($this->callbacks[$indexColumn] != null) {
                return $this->callbacks[$indexColumn]($this->data[$indexRow], $indexRow);
            }
            $key = $this->data->pluck($this->dataTableSet[$indexColumn]['key'])[$indexRow];
            return $this->setSearchWord($key);
        }
        if ($this->dataTableSet[$indexColumn]['index'] != null) {
            return ($indexRow + 1) + (($this->currentPage - 1) * $this->paginate);
        }
        if ($this->dataTableSet[$indexColumn]['key'] == null && $this->dataTableSet[$indexColumn]['index'] == null) {
            return $this->callbacks[$indexColumn]($this->data[$indexRow], $indexRow);
        }
    }

    public function setSearchWord($words)
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

    public function enableBulk(?callable $callback = null)
    {
        $this->bulkCallback = $callback ?? fn($data, $i) => true;
        return $this;
    }

    public function isBulkEnabled($indexRow)
    {
        if ($this->bulkCallback === null) return true;
        return ($this->bulkCallback)($this->data[$indexRow], $indexRow);
    }

    public function enableExpand(callable $callback)
    {
        $this->expandCallback = $callback;
        return $this;
    }

    public function hasExpand() { return $this->expandCallback !== null; }

    public function getExpandContent($indexRow)
    {
        if (!$this->expandCallback) return '';
        return ($this->expandCallback)($this->data[$indexRow], $indexRow);
    }

    public function getRowRawData($indexRow) { return $this->data[$indexRow]; }
    public function getDatas() { return $this->data; }

    public function links($view = null)
    {
        return $this->data->links($view);
    }

    public static function getActionView($data, $i, $editable = true, $deletable = true)
    {
        return view('mrcatz::components.ui.datatable-action', [
            'data' => json_encode($data),
            'index' => $i,
            'editable' => $editable,
            'deletable' => $deletable,
        ]);
    }

    public static function getExpandView($data, array $fields)
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
