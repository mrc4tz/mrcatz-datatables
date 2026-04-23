<?php

namespace MrCatz\DataTable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use MrCatz\DataTable\Concerns\HasBulkActions;
use MrCatz\DataTable\Concerns\HasCustomBulkActions;
use MrCatz\DataTable\Concerns\HasExport;
use MrCatz\DataTable\Concerns\HasFilters;

class MrCatzDataTablesComponent extends MrCatzComponent
{
    use WithPagination;
    use HasFilters;
    use HasExport;
    use HasBulkActions;
    use HasCustomBulkActions;

    // Public properties — no strict types to allow child class override without type declaration
    public $tableTitle = '';
    public $prefix = '';

    protected ?MrCatzDataTables $mrCatzDataTables = null;

    // URL-persisted state. Aliases are assigned dynamically by `queryString()`
    // below so two tables on the same page can coexist without colliding on
    // query-string keys — each table's pageName becomes its prefix.
    public $search = '';
    public $p = null;
    public $key = '';
    public $value = '';
    public $multiSort = [];
    public $filterUrlParams = [];

    public $withLoading = false;
    public $showAddButton = true;
    public $showSearch = true;
    public $usePagination = true;
    public $cardContainer = true;
    public $borderContainer = false;
    public $typeSearch = false;
    public $typeSearchWithDelay = false;
    public $typeSearchDelay = '500ms';

    public $filterDebounceDelay = '300ms';

    public $load_start = false;

    public $enableKeyboardNav = true;
    public $enableColumnResize = true;

    public $columnOrder = [];
    public $hiddenColumns = [];
    public $columnWidths = [];

    public $enableColumnVisibility = true;
    public $enableColumnReorder = true;
    public $expandableRows = false; // false, true/'both', 'mobile', 'desktop'
    public $enableColumnSorting = true;
    public $showKeyboardNavNote = false;
    public $tableZebraStyle = true;
    public $stickyHeader = false;
    public $enableRowClick = false;
    public $maxRows = 500;
    public $loadedRows = 0;
    public $hasMoreRows = false;

    /**
     * Whether the mobile breakpoint (<md) renders each row as a stacked
     * card (the default editorial look) or falls back to the same
     * horizontally-scrolling table used at md+ widths.
     *
     * Set to `false` on a child component when your table's columns are
     * dense enough that the card layout hides useful context, or when
     * you just want mobile parity with desktop for a data-first view:
     *
     *     public $showCardOnMobile = false;
     *
     * Flips three things at render time:
     *   1. Mobile card list (`table-content.blade.php`) stops rendering.
     *   2. Desktop table loses its `hidden md:block` gate and shows at
     *      every breakpoint, horizontally scrolling on narrow screens.
     *   3. Mobile toolbar is skipped in favour of the desktop toolbar
     *      at every breakpoint, so search / filter / bulk-action chips
     *      stay visible without the mobile-specific compact layout.
     */
    public $showCardOnMobile = true;

    /**
     * Whether to render the "Load more" button when $usePagination = false
     * and the result set exceeds $maxRows. Default true.
     *
     * Set to false on your child component if you want the table to render
     * exactly $maxRows rows and never offer a load-more affordance — useful
     * for snapshots, print-friendly views, or when $maxRows is a hard
     * product decision rather than a soft cap:
     *
     *     public $showLoadMoreButton = false;
     */
    public $showLoadMoreButton = true;

    public function CreateMrCatzTable(): MrCatzDataTables
    {
        $onDataLoaded = function ($dataBuilder, $data) {
            $this->onDataLoaded($dataBuilder, $data);
        };

        return MrCatzDataTables::with(
            $this->baseQuery(),
            $this->getRowPerPageOption(),
            $this->p,
            $this->usePagination,
            $this->setPageName(),
            $onDataLoaded
        );
    }

    // Override-able methods — no strict types for backward compatibility
    public function onDataLoaded($dataBuilder, $data) {}
    public function setPageName() { return 'page'; }
    public function baseQuery() { return DB::table('users'); }
    public function setView() { return 'mrcatz::components.ui.datatable-js'; }
    public function configTable() { return null; }
    public function ddTable() { return false; }
    public function setTable() { return MrCatzDataTables::with([]); }
    public function getRowPerPageOption() { return [5, 10, 15, 20]; }
    public function showLoading() {}
    public function emptyStateView() { return null; }

    public function rowClicked($data): void
    {
        $this->dispatch(MrCatzEvent::ROW_CLICK, $data, $this->setPageName());
    }

    /**
     * URL query-string alias prefix. When multiple datatables live on the
     * same page they'd otherwise collide on keys like `?search`, `?filter`,
     * `?col_hidden`, etc. By deriving the prefix from `setPageName()`:
     *
     *   - default pageName (`page`) → no prefix → preserves existing URLs
     *     for single-datatable pages.
     *   - custom pageName (e.g. `penyediaPage`) → that value becomes the
     *     prefix → each table gets its own namespace:
     *     `?penyediaPage_search=foo&penyediaPage_col_hidden[]=1`.
     *
     * Override this method on a child class if you need a different scheme,
     * e.g. shorter prefixes or decoupling the URL namespace from pagination.
     */
    public function urlPrefix(): string
    {
        $name = $this->setPageName();
        if ($name === 'page' || $name === '' || $name === null) {
            return '';
        }
        return $name . '_';
    }

    /**
     * Build the query-string mapping dynamically so `urlPrefix()` can namespace
     * aliases at runtime. Livewire's `#[Url]` attribute doesn't support dynamic
     * `as:` values, but the legacy `queryString()` method still works in v3
     * and is merged into the URL binding system via SupportQueryString.
     */
    public function queryString(): array
    {
        $prefix = $this->urlPrefix();

        return [
            'search'          => ['as' => $prefix . 'search',     'except' => ''],
            'p'               => ['as' => $prefix . 'per_page'],
            'key'             => ['as' => $prefix . 'sort',       'except' => ''],
            'value'           => ['as' => $prefix . 'dir',        'except' => ''],
            'multiSort'       => ['as' => $prefix . 'sort_multi', 'except' => []],
            'filterUrlParams' => ['as' => $prefix . 'filter',     'except' => []],
            'columnOrder'     => ['as' => $prefix . 'col_order',  'except' => []],
            'hiddenColumns'   => ['as' => $prefix . 'col_hidden', 'except' => []],
            'columnWidths'    => ['as' => $prefix . 'col_widths', 'except' => []],
        ];
    }

    public function mount(): void
    {
        $this->prefix = uniqid();
        if ($this->p === null) {
            $this->p = $this->getRowPerPageOption()[0];
        }

        // Validate debounce format (must be like '500ms' or '1s')
        if ($this->typeSearchWithDelay && !preg_match('/^\d+(ms|s)$/', $this->typeSearchDelay)) {
            $this->typeSearchDelay = '500ms';
        }
        if (!preg_match('/^\d+(ms|s)$/', $this->filterDebounceDelay)) {
            $this->filterDebounceDelay = '300ms';
        }

        // Initialize hiddenColumns from column visible defaults (only if URL didn't set them)
        if ($this->enableColumnVisibility && empty($this->hiddenColumns)) {
            $dt = $this->setTable();
            foreach ($dt->getDataTableSet() as $i => $col) {
                if (!($col['visible'] ?? true)) {
                    $this->hiddenColumns[] = $i;
                }
            }
        }

        $this->sanitizeUrlParams();
        $this->bootFilters();
    }

    private function sanitizeUrlParams(): void
    {
        $dt = $this->setTable();
        $maxIndex = $dt->countColumn() - 1;

        // Validate col_hidden — remove invalid indices
        $this->hiddenColumns = array_values(array_filter($this->hiddenColumns, fn($v) => is_numeric($v) && $v >= 0 && $v <= $maxIndex));

        // Validate col_order — reset if any index is invalid
        if (!empty($this->columnOrder)) {
            $valid = true;
            foreach ($this->columnOrder as $v) {
                if (!is_numeric($v) || $v < 0 || $v > $maxIndex) { $valid = false; break; }
            }
            if (!$valid) $this->columnOrder = [];
        }

        // Validate col_widths — remove invalid keys
        if (!empty($this->columnWidths)) {
            $this->columnWidths = array_filter($this->columnWidths, fn($v, $k) => is_numeric($k) && $k >= 0 && $k <= $maxIndex && is_numeric($v), ARRAY_FILTER_USE_BOTH);
        }
    }


    public function render(): mixed
    {
        $this->dataFilters = $this->getDataFilter();
        // Apply any runtime overrides (from setFilterData / setFilterDateBounds)
        // BEFORE the engine reads activeFilters — see HasFilters::applyFilterOverrides.
        $this->applyFilterOverrides();
        $posts = $this->getData();
        $this->hasMoreRows = $posts->hasMoreRows();
        return view($this->setView(), [
            'posts' => $posts,
            'filters' => $this->dataFilters,
            'emptyStateView' => $this->emptyStateView(),
        ]);
    }

    public function loadMore(): void
    {
        $this->loadedRows += $this->maxRows;
        $this->mrCatzDataTables = null;
    }

    public function setSearchWord(?string $words): string
    {
        $words = $words ?? '';
        if ($this->mrCatzDataTables != null) {
            return $this->mrCatzDataTables->setSearchWord($words);
        }
        return $words;
    }

    public function getMrCatzDataTables(): ?MrCatzDataTables { return $this->mrCatzDataTables; }

    private function getData(): MrCatzDataTables
    {
        if ($this->mrCatzDataTables == null) {
            $this->mrCatzDataTables = $this->setData();
        }
        return $this->mrCatzDataTables;
    }

    private function setData(?callable $onFinish = null): MrCatzDataTables
    {
        $dt = $this->setTable();
        $this->applyStateToEngine($dt);
        $result = $dt->build();
        if ($onFinish) $onFinish();
        return $result;
    }

    private function applyStateToEngine(MrCatzDataTables $dt): void
    {
        $dt->setSearch($this->search);
        $dt->setFilters($this->buildKeyValue(), $this->buildFilterCallbacks());
        $dt->setConfig($this->configTable());

        if (!empty($this->multiSort)) {
            $dt->setMultiSort($this->multiSort);
        } else {
            $dt->setOrderByKey($this->key, $this->value);
        }

        $dt->setPaginate($this->p);
        $dt->setCurrentPage($this->getPage($this->setPageName()));

        if (!$this->usePagination && $this->maxRows > 0) {
            $dt->setMaxRows($this->loadedRows > 0 ? $this->loadedRows : $this->maxRows);
        }
    }

    public function inlineUpdate($rowData, $columnKey, $newValue, $rowIndex = null): void
    {
        $dt = $this->setTable();
        $allRules = $dt->getInlineValidationRules();

        if (isset($allRules[$columnKey])) {
            // Strip table prefix (e.g. 'products.name' → 'name') so Laravel
            // doesn't interpret the dot as nested array notation.
            $validationKey = str_contains($columnKey, '.') ? substr($columnKey, strrpos($columnKey, '.') + 1) : $columnKey;

            $validator = Validator::make(
                [$validationKey => $newValue],
                [$validationKey => $allRules[$columnKey]]
            );

            if ($validator->fails()) {
                $error = $validator->errors()->first($validationKey);
                $this->dispatch('inline-validation-error', cellId: $rowIndex . '_' . $columnKey, error: $error);
                $this->notice('error', $error);
                return;
            }
        }

        $this->dispatch(MrCatzEvent::INLINE_UPDATE, $rowData, $columnKey, $newValue, $this->setPageName());
        $this->dispatch('inline-save-done', cellId: $rowIndex . '_' . $columnKey);
    }

    // Dispatches include the table's own `setPageName()` so a single page
    // component can host multiple CRUDs (each datatable has its own
    // setPageName). datatable-scripts.blade relays the pageName into the
    // page's prepareAddData / prepareEditData / prepareDeleteData dispatch
    // so `listenAddData` etc. can set `currentCrudPageName` before invoking
    // the user hook and before the form-builder calls `setForm($pageName)`.
    // Positional payloads so the JS listeners in datatable-scripts can index
    // them as arrays (`d[0]`, `d[1]`). Named-arg dispatches arrive as objects
    // on the JS side and break the index access — silently forwarding a null
    // `$data` to the server's prepareEditData / prepareDeleteData, which then
    // fails dependency resolution because `$data` is a required parameter.
    public function addData(): void    { $this->dispatch(MrCatzEvent::ADD_DATA,    $this->setPageName()); }
    public function editData($data): void   { $this->dispatch(MrCatzEvent::EDIT_DATA,   $data, $this->setPageName()); }
    public function deleteData($data): void { $this->dispatch(MrCatzEvent::DELETE_DATA, $data, $this->setPageName()); }

    #[On(MrCatzEvent::SEARCH_TYPING)]
    public function searchData(): void
    {
        $this->setPage(1, $this->setPageName());
        $this->loadedRows = 0;
        $this->clearSelection();
        $this->findData();
    }

    public function resetData(): void
    {
        $this->setPage(1, $this->setPageName());
        $this->search = '';
        $this->key = '';
        $this->value = '';
        $this->multiSort = [];
        $this->activeFilters = [];
        $this->filterUrlParams = [];
        $this->columnOrder = [];
        $this->hiddenColumns = [];
        $this->columnWidths = [];
        $this->loadedRows = 0;
        $this->clearSelection();
        $this->dispatch(MrCatzEvent::RESET_SELECT, $this->getDataFilter(), $this->prefix);
        $this->mrCatzDataTables = $this->setData();
    }

    public function orderData($key, $order): void
    {
        $this->key = $key;
        $this->value = ($order == 'desc') ? 'asc' : 'desc';
        $this->multiSort = [];
        $this->findData();
    }

    public function addSort($key, $order): void
    {
        $newDir = ($order == 'desc') ? 'asc' : 'desc';

        // Update existing or add new
        foreach ($this->multiSort as $i => $s) {
            if ($s['key'] === $key) {
                $this->multiSort[$i]['dir'] = $newDir;
                $this->findData();
                return;
            }
        }

        // First entry: include the current primary sort
        if (empty($this->multiSort) && !empty($this->key)) {
            $this->multiSort[] = ['key' => $this->key, 'dir' => $this->value];
        }

        $this->multiSort[] = ['key' => $key, 'dir' => $newDir];
        $this->key = '';
        $this->value = '';
        $this->findData();
    }

    public function goToP($page, $pageName = 'page'): void { $this->gotoPage($page, $pageName); }

    public function paginate($perPage): void
    {
        $this->setPage(1, $this->setPageName());
        $this->p = $perPage;
        $this->clearSelection();
        $this->findData();
    }

    private function findData(): void
    {
        if ($this->mrCatzDataTables == null) {
            $this->mrCatzDataTables = $this->setTable();
        }
        $this->applyStateToEngine($this->mrCatzDataTables);
        $this->mrCatzDataTables->build();
    }

    #[On(MrCatzEvent::REFRESH_TABLE)]
    public function refreshData(): void
    {
        $this->load_start = true;
        $this->clearSelection();
        $this->mrCatzDataTables = $this->setData(function () {
            $this->load_start = false;
        });
    }

    public function toggleColumn($columnIndex): void
    {
        $idx = array_search($columnIndex, $this->hiddenColumns);
        if ($idx !== false) {
            array_splice($this->hiddenColumns, $idx, 1);
        } else {
            $this->hiddenColumns[] = $columnIndex;
        }
    }

    public function reorderColumn(int $from, int $to, int $totalColumns): void
    {
        if (empty($this->columnOrder)) {
            $this->columnOrder = range(0, $totalColumns - 1);
        }
        $item = $this->columnOrder[$from];
        array_splice($this->columnOrder, $from, 1);
        array_splice($this->columnOrder, $to, 0, [$item]);
    }
}
