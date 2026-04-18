<?php

namespace MrCatz\DataTable;

use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MrCatzExport implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    private string $format = 'xlsx';
    private bool $hasIndexCol = false;
    private array $conditions = [];

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

    public function setConditions(array $conditions): static
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function view(): View
    {
        $viewName = view()->exists('exports.datatable-excel')
            ? 'exports.datatable-excel'
            : 'mrcatz::exports.datatable-excel';

        return view($viewName, [
            'title' => $this->title,
            'headers' => $this->headers,
            'rows' => $this->rows,
            'format' => $this->format,
            'hasIndexCol' => $this->hasIndexCol,
            'conditions' => $this->conditions,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        // CSV writer ignores cell styling, but `mergeCells()` mutates
        // the underlying spreadsheet BEFORE the writer runs —
        // consolidating multi-cell values into the anchor cell. That
        // collapses the CSV title banner to empty rows, so for CSV we
        // skip every sheet-mutation here entirely.
        if ($this->format === 'csv') {
            return [];
        }

        $c = self::colors();
        $colCount = count($this->headers);
        $lastCol = self::columnLetter($colCount);

        // Layout shifts when conditions banner is included:
        //   rows 1-2 always = title + meta, row 3+ depends on conditions.
        $condCount = count($this->conditions);
        $hasCond = $condCount > 0;
        $condBannerRow = $hasCond ? 3 : null;
        $condStart = $hasCond ? 4 : null;
        $condEnd = $hasCond ? (3 + $condCount) : null;
        $spacerRow = $hasCond ? (4 + $condCount) : 3;
        $headerRow = $hasCond ? (5 + $condCount) : 4;
        $dataStart = $headerRow + 1;
        $lastRow = $headerRow + count($this->rows);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A{$spacerRow}:{$lastCol}{$spacerRow}");
        $sheet->getRowDimension($spacerRow)->setRowHeight(8);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);
        $sheet->freezePane("A{$dataStart}");

        if ($hasCond) {
            $sheet->mergeCells("A{$condBannerRow}:{$lastCol}{$condBannerRow}");
            for ($r = $condStart; $r <= $condEnd; $r++) {
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            }
        }

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

        $sheet->getStyle("A{$dataStart}:A{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => $c['title_text']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => $c['subtitle_text']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            $headerRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $c['header_text']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $c['header_bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            "A{$headerRow}:{$lastCol}{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $c['border']]]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
            "A{$headerRow}:{$lastCol}{$headerRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $c['header_border']]]],
            ],
            "A{$lastRow}:{$lastCol}{$lastRow}" => [
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $c['bottom_border']]]],
            ],
        ];

        if ($hasCond) {
            $styles[$condBannerRow] = [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $c['title_text']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $styles["A{$condStart}:{$lastCol}{$condEnd}"] = [
                'font' => ['size' => 9, 'color' => ['rgb' => $c['subtitle_text']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
        }

        return $styles;
    }

    public static function columnLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col = intdiv($col, 26);
        }
        return $letter;
    }

    /**
     * Get export colors from config with defaults.
     */
    public static function colors(): array
    {
        $defaults = [
            'header_bg'     => '1B3A5C',
            'header_text'   => 'FFFFFF',
            'header_border' => '0F2942',
            'title_text'    => '1B3A5C',
            'subtitle_text' => '6B7280',
            'border'        => 'D1D5DB',
            'stripe'        => 'F0F4F8',
            'bottom_border' => '1B3A5C',
        ];

        return array_merge($defaults, config('mrcatz.export_colors', []));
    }
}
