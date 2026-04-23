<?php

namespace MrCatz\DataTable\Concerns;

use MrCatz\DataTable\MrCatzEvent;

trait HasBulkActions
{
    public $bulkPrimaryKey = null;
    public $showBulkButton = false;
    public $bulkActive = false;
    public $selectedRows = [];
    public $selectAll = false;

    public function toggleBulk(): void
    {
        $this->bulkActive = !$this->bulkActive;
        if (!$this->bulkActive) { $this->clearSelection(); }
    }

    public function toggleSelectAll(): void
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

    public function clearSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedRows)) return;
        $rows = $this->selectedRows;
        $this->clearSelection();
        $this->dispatch(MrCatzEvent::BULK_DELETE, $rows, $this->setPageName());
    }
}
