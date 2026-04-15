<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use MrCatz\DataTable\MrCatzExport;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DatatableExport implements FromView, ShouldAutoSize, WithStyles
{
    private string $format = 'xlsx';
    private bool $hasIndexCol = false;

    public function __construct(
        private string $title,
        private array $headers,
        private array $rows
    ) {}

    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function setHasIndexCol(bool $hasIndexCol): static
    {
        $this->hasIndexCol = $hasIndexCol;
        return $this;
    }

    public function view(): View
    {
        return view('exports.datatable-excel', [
            'title' => $this->title,
            'headers' => $this->headers,
            'rows' => $this->rows,
            'format' => $this->format,
            'hasIndexCol' => $this->hasIndexCol,
        ]);
    }

    /**
     * Customize Excel styles here.
     * Colors are loaded from config/mrcatz.php 'export_colors'.
     * Use MrCatzExport::columnLetter($n) to convert column number to letter.
     * Use MrCatzExport::colors() to get color config with defaults.
     */
    public function styles(Worksheet $sheet): array
    {
        // CSV writer ignores styling, and mergeCells() would collapse
        // the title banner cells into the (empty) anchor column. Skip
        // all sheet mutations for CSV.
        if ($this->format === 'csv') {
            return [];
        }

        $c = MrCatzExport::colors();
        $lastCol = MrCatzExport::columnLetter(count($this->headers));
        $headerRow = 4;
        $dataStart = 5;
        $lastRow = $headerRow + count($this->rows);

        // Merge title, subtitle, spacer
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getRowDimension(3)->setRowHeight(8);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // Freeze pane below header
        $sheet->freezePane("A{$dataStart}");

        // Zebra striping
        for ($r = $dataStart; $r <= $lastRow; $r++) {
            if (($r - $dataStart) % 2 === 1) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $c['stripe']],
                    ],
                ]);
            }
        }

        // First column center
        $sheet->getStyle("A{$dataStart}:A{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            // Title
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $c['title_text']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Subtitle
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => $c['subtitle_text']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Column headers
            $headerRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $c['header_text']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $c['header_bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Data borders
            "A{$headerRow}:{$lastCol}{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $c['border']]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
            // Header border darker
            "A{$headerRow}:{$lastCol}{$headerRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $c['header_border']]]],
            ],
            // Bottom border
            "A{$lastRow}:{$lastCol}{$lastRow}" => [
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $c['bottom_border']]]],
            ],
        ];
    }
}
