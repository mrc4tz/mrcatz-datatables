<?php

namespace MrCatz\DataTable;

use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MrCatzExport implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles
{
    public function __construct(
        private string $title,
        private array $headers,
        private array $rows
    ) {}

    public function view(): View
    {
        $viewName = view()->exists('exports.datatable-excel')
            ? 'exports.datatable-excel'
            : 'mrcatz::exports.datatable-excel';

        return view($viewName, [
            'title' => $this->title,
            'headers' => $this->headers,
            'rows' => $this->rows,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $c = self::colors();
        $colCount = count($this->headers);
        $lastCol = self::columnLetter($colCount);
        $headerRow = 4;
        $dataStart = 5;
        $lastRow = $headerRow + count($this->rows);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getRowDimension(3)->setRowHeight(8);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);
        $sheet->freezePane("A{$dataStart}");

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

        return [
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
