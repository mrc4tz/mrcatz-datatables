<?php

namespace MrCatz\DataTable\Concerns;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use MrCatz\DataTable\MrCatzDataTables;
use MrCatz\DataTable\MrCatzEvent;

trait HasExport
{
    public $showExportButton = true;
    public $exportTitle = 'Data Export';
    public $exportSearch = '';
    public $exportFilterValues = [];
    public $exportCount = 0;

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

    // Override-able export hooks
    public function beforeExport(array $headers, array $rows, string $format, string $scope) { return ['headers' => $headers, 'rows' => $rows]; }
    public function afterExport(string $format, string $scope) {}

    public function exportData(string $format, string $scope = 'filtered'): mixed
    {
        if ($format === 'pdf' && !class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $this->notice('error', 'PDF export memerlukan barryvdh/laravel-dompdf. Jalankan: composer require barryvdh/laravel-dompdf');
            return null;
        }
        if (in_array($format, ['xlsx', 'excel', 'csv'], true) && !class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $this->notice('error', 'Excel/CSV export memerlukan maatwebsite/excel. Jalankan: composer require maatwebsite/excel');
            return null;
        }

        try {
            $exportData = $this->buildExportData($scope);
        } catch (\Throwable $e) {
            $this->notice('error', 'Gagal memproses data export: ' . $e->getMessage());
            return null;
        }

        $processed = $this->beforeExport($exportData['headers'], $exportData['rows'], $format, $scope);
        $headers = $processed['headers'];
        $rows = $processed['rows'];
        $title = $this->exportTitle ?: $this->title ?: 'Export';
        $filename = str_replace(' ', '_', strtolower($title)) . '_' . now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdfView = view()->exists('exports.datatable-pdf')
                ? 'exports.datatable-pdf'
                : 'mrcatz::exports.datatable-pdf';

            $pdf = Pdf::loadView($pdfView, [
                'title' => $title, 'headers' => $headers, 'rows' => $rows,
            ])->setPaper('a4', 'landscape');

            $this->afterExport($format, $scope);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename . '.pdf');
        }

        try {
            $exportClass = class_exists(\App\Exports\DatatableExport::class)
                ? new \App\Exports\DatatableExport($title, $headers, $rows)
                : new \MrCatz\DataTable\MrCatzExport($title, $headers, $rows);

            if (method_exists($exportClass, 'setFormat')) {
                $exportClass->setFormat($format);
            }
            if (method_exists($exportClass, 'setHasIndexCol')) {
                $exportClass->setHasIndexCol($exportData['hasIndexCol'] ?? false);
            }

            $this->afterExport($format, $scope);

            if ($format === 'csv') {
                return Excel::download($exportClass, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }

            return Excel::download($exportClass, $filename . '.xlsx');
        } catch (\Throwable $e) {
            $this->notice('error', 'Gagal export ' . ($format === 'csv' ? 'CSV' : 'Excel') . ': ' . $e->getMessage());
            return null;
        }
    }

    protected int $exportChunkSize = 500;

    protected function buildExportData(string $scope): array
    {
        $dt = $this->setTable();
        $dataTableSet = $dt->getDataTableSet();

        $headers = [];
        $exportableColumns = [];
        foreach ($dataTableSet as $i => $col) {
            // Skip explicit action columns (withActionColumn() or
            // withCustomColumn(..., type: 'action')). Display-only custom
            // columns like computed per-row values or dynamic year columns
            // must still be exported — they're data, not UI.
            if (($col['type'] ?? null) === 'action') {
                continue;
            }
            // Skip image columns
            if (($col['type'] ?? null) === 'image') {
                continue;
            }
            $headers[] = $col['head'];
            $exportableColumns[] = $i;
        }

        $baseQuery = $this->buildExportQuery($scope)->orderBy('created_at', 'desc');
        $rows = [];
        $globalIndex = 0;

        $baseQuery->chunk($this->exportChunkSize, function ($chunk) use (&$rows, &$globalIndex, $dataTableSet, $exportableColumns) {
            $chunkDt = $this->setTable();
            $chunkDt->setExportData($chunk);

            for ($rowIndex = 0; $rowIndex < $chunkDt->countRow(); $rowIndex++) {
                $row = [];
                foreach ($exportableColumns as $colIndex) {
                    $col = $dataTableSet[$colIndex];
                    if ($col['index'] !== null) {
                        $row[] = ++$globalIndex;
                    } else {
                        $row[] = strip_tags($chunkDt->getData($rowIndex, $colIndex) ?? '');
                    }
                }
                $rows[] = $row;
            }
        });

        $firstExportableCol = $exportableColumns[0] ?? null;
        $hasIndexCol = $firstExportableCol !== null
            && ($dataTableSet[$firstExportableCol]['index'] ?? null) !== null;

        return ['headers' => $headers, 'rows' => $rows, 'hasIndexCol' => $hasIndexCol];
    }
}
