<?php

namespace MrCatz\DataTable;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;

class MrCatzDataTablesComponent extends MrCatzComponent
{
    use WithPagination;

    public $tableTitle = '';
    public $prefix = '';

    protected $mrCatzDataTables = null;
    public $dataFilters = [];
    public $activeFilters = [];
    public $filterShow = [];
    public $filterData = [];

    #[Url(except: '')]
    public $search = '';

    #[Url(as: 'per_page')]
    public $p = null;

    #[Url(as: 'sort', except: '')]
    public $key = '';

    #[Url(as: 'dir', except: '')]
    public $value = '';

    #[Url(as: 'filter', except: [])]
    public $filterUrlParams = [];

    public $default_filter_value = '';

    public $withLoading = false;
    public $showAddButton = true;
    public $showSearch = true;
    public $usePagination = true;
    public $cardContainer = true;
    public $borderContainer = false;
    public $typeSearch = false;
    public $typeSearchWithDelay = false;
    public $typeSearchDelay = '500ms';

    public $load_start = false;
    public $showExportButton = true;
    public $exportTitle = 'Data Export';

    public $exportSearch = '';
    public $exportFilterValues = [];
    public $exportCount = 0;

    public $bulkPrimaryKey = null;
    public $showBulkButton = false;
    public $bulkActive = false;
    public $selectedRows = [];
    public $selectAll = false;

    public $enableKeyboardNav = true;
    public $enableColumnResize = true;
    public $columnOrder = [];
    public $enableColumnReorder = true;
    public $expandableRows = false;
    public $enableColumnSorting = true;
    public $showKeyboardNavNote = false;
    public $tableZebraStyle = true;

    public function CreateMrCatzTable()
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

    public function onDataLoaded($dataBuilder, $data) {}
    public function setPageName() { return 'page'; }

    public function mount()
    {
        $this->prefix = uniqid();
        if ($this->p === null) {
            $this->p = $this->getRowPerPageOption()[0];
        }
        foreach ($this->setFilter() as $f => $filter) {
            $this->filterShow[$f] = $filter->getDataFilter()['show'];
            $this->filterData[$f] = $filter->getDataFilter()['data'];
        }

        if (!empty($this->filterUrlParams)) {
            foreach ($this->filterUrlParams as $id => $value) {
                $config = $this->findFilterConfigById($id);
                if ($config) {
                    $this->activeFilters[] = [
                        'id' => $id,
                        'key' => $config['key'],
                        'value' => $value,
                        'condition' => $config['condition'],
                    ];
                }
            }
        }
    }

    public function setFilterShow($id, $show)
    {
        foreach ($this->setFilter() as $f => $filter) {
            if ($filter->getDataFilter()['id'] == $id) {
                $this->filterShow[$f] = $show;
                return;
            }
        }
    }

    private function findFilterConfigById($id)
    {
        foreach ($this->setFilter() as $filter) {
            $df = $filter->getDataFilter();
            if ($df['id'] == $id) return $df;
        }
        return null;
    }

    private function syncFilterUrl()
    {
        $this->filterUrlParams = [];
        foreach ($this->activeFilters as $af) {
            if (!empty($af['value'])) {
                $this->filterUrlParams[$af['id']] = $af['value'];
            }
        }
    }

    private function getDataFilter()
    {
        $df = [];
        foreach ($this->setFilter() as $filter) {
            array_push($df, $filter->getDataFilter());
        }
        return $df;
    }

    public function render()
    {
        $this->dataFilters = $this->getDataFilter();
        return view($this->setView(), [
            'posts' => $this->getData(),
            'filters' => $this->dataFilters
        ]);
    }

    public function setSearchWord($words)
    {
        if ($this->mrCatzDataTables != null) {
            return $this->mrCatzDataTables->setSearchWord($words);
        }
        return $words;
    }

    public function baseQuery() { return DB::table('users'); }
    public function setView() { return 'mrcatz::components.ui.datatable-js'; }
    public function getMrCatzDataTables() { return $this->mrCatzDataTables; }

    private function getData()
    {
        if ($this->mrCatzDataTables == null) {
            $this->mrCatzDataTables = $this->setData();
        }
        return $this->mrCatzDataTables;
    }

    public function configTable() { return null; }

    private function setData($onFinish = null)
    {
        $dt = $this->setTable();
        $this->applyStateToEngine($dt);
        $result = $dt->build();
        if ($onFinish) $onFinish();
        return $result;
    }

    private function applyStateToEngine(MrCatzDataTables $dt)
    {
        $dt->setSearch($this->search);
        $dt->setFilters($this->buildKeyValue(), $this->buildFilterCallbacks());
        $dt->setConfig($this->configTable());
        $dt->setOrderByKey($this->key, $this->value);
        $dt->setPaginate($this->p);
        $dt->setCurrentPage($this->getPage($this->setPageName()));
    }

    private function buildKeyValue() { return array_values($this->activeFilters); }

    private function buildFilterCallbacks()
    {
        $callbacks = [];
        foreach ($this->activeFilters as $filter) {
            $callbacks[] = $this->findFilterCallbackById($filter['id']);
        }
        return $callbacks;
    }

    public function ddTable() { return false; }
    public function setTable() { return MrCatzDataTables::with([]); }

    public function addData() { $this->dispatch('add-data'); }
    public function editData($data) { $this->dispatch('edit-data', $data); }
    public function deleteData($data) { $this->dispatch('delete-data', $data); }

    #[On('search-typing')]
    public function searchData()
    {
        $this->setPage(1);
        $this->clearSelection();
        $this->findData();
    }

    public function resetData()
    {
        $this->setPage(1);
        $this->search = '';
        $this->key = '';
        $this->value = '';
        $this->activeFilters = [];
        $this->filterUrlParams = [];
        $this->clearSelection();
        $this->dispatch('reset-select', $this->getDataFilter(), $this->prefix);
        $this->mrCatzDataTables = $this->setData();
    }

    public function orderData($key, $order)
    {
        $this->key = $key;
        $this->value = ($order == 'desc') ? 'asc' : 'desc';
        $this->findData();
    }

    public function setFilter() { return []; }

    public function goToP($page, $pageName = 'page') { $this->gotoPage($page, $pageName); }

    public function paginate($perPage)
    {
        $this->setPage(1);
        $this->p = $perPage;
        $this->clearSelection();
        $this->findData();
    }

    // Dipanggil dari blade via wire:change
    public function change($id, $value)
    {
        $filter = $this->findFilterById($id);
        $filterValue = $value === '' ? null : $value;

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
                'id' => $id,
                'key' => $filter['key'],
                'value' => $filterValue,
                'condition' => $filter['condition'],
            ];
        }

        $this->syncFilterUrl();
        $this->setPage(1);
        $this->clearSelection();
        $this->findData();
        $this->onFilterChanged($id, $filterValue);
    }

    // Override di child class untuk react terhadap perubahan filter
    public function onFilterChanged($id, $value) {}

    private function findData()
    {
        if ($this->mrCatzDataTables == null) {
            $this->mrCatzDataTables = $this->setTable();
        }
        $this->applyStateToEngine($this->mrCatzDataTables);
        $this->mrCatzDataTables->build();
    }

    private function findFilterById($id)
    {
        foreach ($this->dataFilters as $filter) {
            if ($filter['id'] == $id) return $filter;
        }
        return [];
    }

    private function findFilterCallbackById($id)
    {
        foreach ($this->setFilter() as $filter) {
            if ($filter->getDataFilter()['id'] == $id) return $filter->getCallback();
        }
        return null;
    }

    public function getRowPerPageOption() { return [5, 10, 15, 20]; }

    #[On('refreshDataTable')]
    public function refreshData()
    {
        $this->load_start = true;
        $this->clearSelection();
        $this->mrCatzDataTables = $this->setData(function () {
            $this->load_start = false;
        });
    }

    public function showLoading() {}

    // Bulk actions

    public function toggleBulk()
    {
        $this->bulkActive = !$this->bulkActive;
        if (!$this->bulkActive) { $this->clearSelection(); }
    }

    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;
        if ($this->selectAll) {
            if ($this->mrCatzDataTables == null) { $this->mrCatzDataTables = $this->setData(); }
            $data = $this->mrCatzDataTables->getDatas();
            $ids = [];
            foreach ($data as $i => $row) {
                if ($this->mrCatzDataTables->isBulkEnabled($i)) {
                    $ids[] = (string) $row->{$this->bulkPrimaryKey};
                }
            }
            $this->selectedRows = $ids;
        } else {
            $this->selectedRows = [];
        }
    }

    public function clearSelection() { $this->selectedRows = []; $this->selectAll = false; }

    public function bulkDelete()
    {
        if (empty($this->selectedRows)) return;
        $rows = $this->selectedRows;
        $this->clearSelection();
        $this->dispatch('bulkDeleteData', selectedRows: $rows);
    }

    public function reorderColumn($from, $to, $totalColumns)
    {
        if (empty($this->columnOrder)) {
            $this->columnOrder = range(0, $totalColumns - 1);
        }
        $item = $this->columnOrder[$from];
        array_splice($this->columnOrder, $from, 1);
        array_splice($this->columnOrder, $to, 0, [$item]);
    }

    // Export

    public function openExportModal()
    {
        $this->exportSearch = $this->search;
        $this->exportFilterValues = [];

        foreach ($this->setFilter() as $filter) {
            $df = $filter->getDataFilter();
            $id = $df['id'];
            $this->exportFilterValues[$id] = null;

            foreach ($this->activeFilters as $af) {
                if ($af['id'] === $id) {
                    $this->exportFilterValues[$id] = $af['value'];
                    break;
                }
            }
        }

        $this->updateExportCount('filtered');
        $this->dispatch('open-export-modal');
    }

    public function updateExportCount(string $scope = 'filtered')
    {
        $this->exportCount = $this->buildExportQuery($scope)->count();
    }

    protected function buildExportQuery(string $scope)
    {
        $query = clone $this->baseQuery();

        if ($scope === 'filtered') {
            if (!empty($this->exportSearch)) {
                $dt = $this->setTable();
                $searchableColumns = collect($dt->getDataTableSet())->filter(fn($d) => $d['key'] !== null)->toArray();
                $query = MrCatzDataTables::applySearchWhere($query, $this->exportSearch, $searchableColumns);
            }

            foreach ($this->setFilter() as $filter) {
                $df = $filter->getDataFilter();
                $id = $df['id'];
                $value = $this->exportFilterValues[$id] ?? null;

                if (!empty($value)) {
                    $callback = $filter->getCallback();
                    if ($callback != null) { $query = $callback($query, $value); }
                    elseif ($df['key'] != '-') { $query = $query->where($df['key'], $df['condition'], $value); }
                }
            }
        }

        return $query;
    }

    public function exportData(string $format, string $scope = 'filtered')
    {
        $exportData = $this->buildExportData($scope);
        $headers = $exportData['headers'];
        $rows = $exportData['rows'];
        $title = $this->exportTitle ?: $this->title ?: 'Export';
        $filename = str_replace(' ', '_', strtolower($title)) . '_' . now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.datatable-pdf', [
                'title' => $title, 'headers' => $headers, 'rows' => $rows,
            ])->setPaper('a4', 'landscape');

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename . '.pdf');
        }

        return Excel::download(
            new \App\Exports\DatatableExport($title, $headers, $rows),
            $filename . '.xlsx'
        );
    }

    protected function buildExportData(string $scope): array
    {
        $dt = $this->setTable();
        $dataTableSet = $dt->getDataTableSet();

        $headers = [];
        $exportableColumns = [];
        foreach ($dataTableSet as $i => $col) {
            if ($col['key'] !== null || $col['index'] !== null) {
                $headers[] = $col['head'];
                $exportableColumns[] = $i;
            }
        }

        $data = $this->buildExportQuery($scope)->orderBy('created_at', 'desc')->get();

        $rows = [];
        foreach ($data as $rowIndex => $record) {
            $row = [];
            foreach ($exportableColumns as $colIndex) {
                $col = $dataTableSet[$colIndex];
                if ($col['index'] !== null) { $row[] = $rowIndex + 1; }
                elseif ($col['key'] !== null) { $row[] = strip_tags($record->{$col['key']} ?? ''); }
            }
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }
}
