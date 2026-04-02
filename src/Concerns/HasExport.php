<?php

namespace MrCatz\DataTable\Concerns;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\MrCatzEvent;

trait HasExport
{
    public bool $showExportButton = true;
    public string $exportTitle = 'Data Export';
    public string $exportSearch = '';
    public array $exportFilterValues = [];
    public int $exportCount = 0;

    public function openExportModal(): void
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
        $this->dispatch(MrCatzEvent::OPEN_EXPORT_MODAL);
    }

    public function updateExportCount(string $scope = 'filtered'): void
    {
        $this->exportCount = $this->buildExportQuery($scope)->count();
    }

    protected function buildExportQuery(string $scope): mixed
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

    public function exportData(string $format, string $scope = 'filtered'): mixed
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

        $exportClass = class_exists(\App\Exports\DatatableExport::class)
            ? new \App\Exports\DatatableExport($title, $headers, $rows)
            : new \MrCatz\DataTable\MrCatzExport($title, $headers, $rows);

        return Excel::download($exportClass, $filename . '.xlsx');
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
