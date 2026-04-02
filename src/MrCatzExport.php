<?php

namespace MrCatz\DataTable;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MrCatzExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string $title,
        private array $headers,
        private array $rows
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function title(): string
    {
        return $this->title;
    }
}
