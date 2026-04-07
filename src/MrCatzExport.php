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
                        'startColor' => ['rgb' => 'F0F4F8'],
                    ],
                ]);
            }
        }

        $sheet->getStyle("A{$dataStart}:A{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1B3A5C']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '6B7280']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            $headerRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B3A5C']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            "A{$headerRow}:{$lastCol}{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
            "A{$headerRow}:{$lastCol}{$headerRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0F2942']]],
            ],
            "A{$lastRow}:{$lastCol}{$lastRow}" => [
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1B3A5C']]],
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
}
