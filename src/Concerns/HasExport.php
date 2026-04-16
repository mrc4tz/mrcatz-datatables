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
    public $exportCount = 0;
    public $exportPreview = [];

    public function openExportModal(): void
    {
        $this->exportPreview = $this->buildExportPreview();
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
            // Apply search (mirrors main table)
            if (!empty($this->search)) {
                $dt = $this->setTable();
                $searchableColumns = collect($dt->getDataTableSet())->filter(fn($d) => $d['key'] !== null)->toArray();
                $query = MrCatzDataTables::applySearchWhere($query, $this->search, $searchableColumns);
            }

            // Apply active filters (mirrors main table engine logic)
            foreach ($this->setFilter() as $filter) {
                $df = $filter->getDataFilter();
                $id = $df['id'];
                $activeValue = null;

                foreach ($this->activeFilters as $af) {
                    if ($af['id'] === $id) {
                        $activeValue = $af['value'];
                        break;
                    }
                }

                if ($activeValue === null || $activeValue === '') continue;

                $callback = $filter->getCallback();
                $type = $df['type'] ?? 'select';

                if ($type === 'date_range') {
                    $from = is_array($activeValue) ? ($activeValue['from'] ?? null) : null;
                    $to   = is_array($activeValue) ? ($activeValue['to']   ?? null) : null;
                    if (!$from && !$to) continue;

                    if ($callback !== null) {
                        $query = $callback($query, ['from' => $from, 'to' => $to]);
                    } elseif ($df['key'] !== '-') {
                        $format = $df['format'] ?? 'date';
                        if ($from) $query = $this->exportDateWhere($query, $df['key'], $format, '>=', $from);
                        if ($to)   $query = $this->exportDateWhere($query, $df['key'], $format, '<=', $to);
                    }
                } elseif ($type === 'date') {
                    if ($callback !== null) {
                        $query = $callback($query, $activeValue);
                    } elseif ($df['key'] !== '-') {
                        $query = $this->exportDateWhere($query, $df['key'], $df['format'] ?? 'date', $df['condition'] ?? '=', $activeValue);
                    }
                } else {
                    if ($callback !== null) {
                        $query = $callback($query, $activeValue);
                    } elseif ($df['key'] !== '-') {
                        $query = $query->where($df['key'], $df['condition'], $activeValue);
                    }
                }
            }

            // Apply bulk selection filter
            if ($this->bulkActive && !empty($this->selectedRows) && $this->bulkPrimaryKey) {
                $pk = $this->bulkPrimaryKey;
                // Qualify with base table name to avoid ambiguity on JOINed queries
                if (!str_contains($pk, '.')) {
                    $pk = $query->from . '.' . $pk;
                }
                $query->whereIn($pk, $this->selectedRows);
            }
        }

        return $query;
    }

    /**
     * Apply a date-aware where clause — mirrors MrCatzDataTables::applyDateComparison.
     */
    private function exportDateWhere($query, string $key, string $format, string $operator, mixed $value)
    {
        return match ($format) {
            'date'            => $query->whereDate($key, $operator, $value),
            'datetime'        => $query->where($key, $operator, $value),
            'time', 'time_hm' => $query->whereTime($key, $operator, $value),
            'year'            => $query->whereYear($key, $operator, $value),
            'month_year'      => $this->exportMonthYearWhere($query, $key, $operator, $value),
            default           => $query->where($key, $operator, $value),
        };
    }

    private function exportMonthYearWhere($query, string $key, string $operator, string $value)
    {
        $parts = explode('-', $value);
        if (count($parts) !== 2) return $query;

        $year  = (int) $parts[0];
        $month = (int) $parts[1];

        if ($operator === '=') {
            return $query->whereYear($key, $year)->whereMonth($key, $month);
        }
        if ($operator === '!=' || $operator === '<>') {
            return $query->where(fn($q) => $q->whereYear($key, '!=', $year)->orWhereMonth($key, '!=', $month));
        }
        if (in_array($operator, ['>', '>='], true)) {
            $strict = $operator === '>';
            return $query->where(fn($q) => $q->whereYear($key, '>', $year)
                ->orWhere(fn($q2) => $q2->whereYear($key, $year)->whereMonth($key, $strict ? '>' : '>=', $month)));
        }
        if (in_array($operator, ['<', '<='], true)) {
            $strict = $operator === '<';
            return $query->where(fn($q) => $q->whereYear($key, '<', $year)
                ->orWhere(fn($q2) => $q2->whereYear($key, $year)->whereMonth($key, $strict ? '<' : '<=', $month)));
        }

        return $query;
    }

    /**
     * Build preview data for the export dialog.
     */
    private function buildExportPreview(): array
    {
        $preview = [];

        // Search
        if (!empty($this->search)) {
            $preview[] = ['icon' => 'search', 'label' => mrcatz_lang('export_search'), 'value' => $this->search];
        }

        // Active filters
        foreach ($this->setFilter() as $f => $filter) {
            $df = $filter->getDataFilter();
            $id = $df['id'];

            $activeValue = null;
            foreach ($this->activeFilters as $af) {
                if ($af['id'] === $id) {
                    $activeValue = $af['value'];
                    break;
                }
            }
            if ($activeValue === null || $activeValue === '') continue;

            $type = $df['type'] ?? 'select';

            if ($type === 'date_range') {
                $from = is_array($activeValue) ? ($activeValue['from'] ?? null) : null;
                $to   = is_array($activeValue) ? ($activeValue['to']   ?? null) : null;
                if (!$from && !$to) continue;
                $display = ($from ?? '...') . '  →  ' . ($to ?? '...');
            } elseif ($type === 'date') {
                $display = $activeValue;
            } else {
                // Resolve select value to display label
                $display = $activeValue;
                foreach ($this->filterData[$f] ?? [] as $data) {
                    if (($data[$df['value']] ?? null) == $activeValue) {
                        $display = $data[$df['option']] ?? $activeValue;
                        break;
                    }
                }
            }

            $icon = match ($type) {
                'date', 'date_range' => 'event',
                default => 'filter_alt',
            };

            $preview[] = ['icon' => $icon, 'label' => $df['label'], 'value' => $display];
        }

        // Sort — strip table prefix for display (e.g. 'demo_products.name' → 'name')
        $sortLabel = function (string $key): string {
            return str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;
        };
        $sorts = [];
        if (!empty($this->multiSort)) {
            foreach ($this->multiSort as $s) {
                $sorts[] = $sortLabel($s['key']) . ' ' . strtoupper($s['dir']);
            }
        } elseif (!empty($this->key)) {
            $sorts[] = $sortLabel($this->key) . ' ' . strtoupper($this->value);
        }
        if (!empty($sorts)) {
            $preview[] = ['icon' => 'sort', 'label' => mrcatz_lang('export_preview_sort'), 'value' => implode(', ', $sorts)];
        }

        // Hidden columns
        if ($this->enableColumnVisibility && !empty($this->hiddenColumns)) {
            $preview[] = ['icon' => 'visibility_off', 'label' => mrcatz_lang('col_visibility'), 'value' => str_replace(':count', count($this->hiddenColumns), mrcatz_lang('export_preview_hidden'))];
        }

        // Bulk selection
        if ($this->bulkActive && !empty($this->selectedRows)) {
            $preview[] = ['icon' => 'checklist', 'label' => mrcatz_lang('export_preview_bulk'), 'value' => count($this->selectedRows) . ' ' . mrcatz_lang('data_selected')];
        }

        return $preview;
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

    /**
     * Check if a column key belongs to a joined table (not the base FROM table).
     * e.g. 'demo_categories.name' on a query FROM 'demo_products' → true
     */
    private function isJoinedTableKey(string $key): bool
    {
        if (!str_contains($key, '.')) return false;
        $tablePrefix = substr($key, 0, strrpos($key, '.'));
        $baseTable = $this->baseQuery()->from ?? '';
        return $tablePrefix !== $baseTable;
    }

    protected int $exportChunkSize = 500;

    protected function buildExportData(string $scope): array
    {
        $dt = $this->setTable();
        $dataTableSet = $dt->getDataTableSet();

        $headers = [];
        $exportableColumns = [];
        foreach ($dataTableSet as $i => $col) {
            if (($col['type'] ?? null) === 'action') continue;
            // Skip hidden columns when exporting filtered data
            if ($scope === 'filtered' && in_array($i, $this->hiddenColumns ?? [])) continue;

            $headers[] = $col['head'];
            $exportableColumns[] = $i;
        }

        $baseQuery = $this->buildExportQuery($scope);

        // Apply sort: use current table sort for filtered scope, default for all
        if ($scope === 'filtered') {
            if (!empty($this->multiSort)) {
                foreach ($this->multiSort as $s) {
                    $baseQuery = $baseQuery->orderBy($s['key'], $s['dir']);
                }
            } elseif (!empty($this->key)) {
                $baseQuery = $baseQuery->orderBy($this->key, $this->value);
            } else {
                $baseQuery = $baseQuery->orderBy('created_at', 'desc');
            }
        } else {
            $baseQuery = $baseQuery->orderBy('created_at', 'desc');
        }
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
                    } elseif ($col['key'] !== null && !($chunkDt->hasCallback($colIndex) && $this->isJoinedTableKey($col['key']))) {
                        // Column with key on the base table (or no callback):
                        // read raw DB value — bypasses callbacks that return HTML
                        $row[] = $chunkDt->getRawKeyData($rowIndex, $colIndex) ?? '';
                    } else {
                        // Joined-table key with callback, or no key at all:
                        // call callback (knows the SELECT alias) and strip HTML
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
