<?php

namespace MrCatz\DataTable;

use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use MrCatz\DataTable\Concerns\HasBulkActions;
use MrCatz\DataTable\Concerns\HasExport;
use MrCatz\DataTable\Concerns\HasFilters;

class MrCatzDataTablesComponent extends MrCatzComponent
{
    use WithPagination;
    use HasFilters;
    use HasExport;
    use HasBulkActions;

    // Public properties — no strict types to allow child class override without type declaration
    public $tableTitle = '';
    public $prefix = '';

    protected ?MrCatzDataTables $mrCatzDataTables = null;

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

    public $enableKeyboardNav = true;
    public $enableColumnResize = true;
    public $columnOrder = [];
    public $enableColumnReorder = true;
    public $expandableRows = false;
    public $enableColumnSorting = true;
    public $showKeyboardNavNote = false;
    public $tableZebraStyle = true;

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

    public function mount(): void
    {
        $this->prefix = uniqid();
        if ($this->p === null) {
            $this->p = $this->getRowPerPageOption()[0];
        }
        $this->bootFilters();
    }

    public function render(): mixed
    {
        $this->dataFilters = $this->getDataFilter();
        return view($this->setView(), [
            'posts' => $this->getData(),
            'filters' => $this->dataFilters
        ]);
    }

    public function setSearchWord(string $words): string
    {
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
        $dt->setOrderByKey($this->key, $this->value);
        $dt->setPaginate($this->p);
        $dt->setCurrentPage($this->getPage($this->setPageName()));
    }

    public function addData(): void { $this->dispatch(MrCatzEvent::ADD_DATA); }
    public function editData($data): void { $this->dispatch(MrCatzEvent::EDIT_DATA, $data); }
    public function deleteData($data): void { $this->dispatch(MrCatzEvent::DELETE_DATA, $data); }

    #[On(MrCatzEvent::SEARCH_TYPING)]
    public function searchData(): void
    {
        $this->setPage(1);
        $this->clearSelection();
        $this->findData();
    }

    public function resetData(): void
    {
        $this->setPage(1);
        $this->search = '';
        $this->key = '';
        $this->value = '';
        $this->activeFilters = [];
        $this->filterUrlParams = [];
        $this->clearSelection();
        $this->dispatch(MrCatzEvent::RESET_SELECT, $this->getDataFilter(), $this->prefix);
        $this->mrCatzDataTables = $this->setData();
    }

    public function orderData($key, $order): void
    {
        $this->key = $key;
        $this->value = ($order == 'desc') ? 'asc' : 'desc';
        $this->findData();
    }

    public function goToP($page, $pageName = 'page'): void { $this->gotoPage($page, $pageName); }

    public function paginate($perPage): void
    {
        $this->setPage(1);
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
